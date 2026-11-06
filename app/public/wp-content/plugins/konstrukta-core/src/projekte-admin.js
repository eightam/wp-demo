/**
 * Admin-Seite «Projekt-Übersicht»: DataViews (Liste/Raster/Tabelle) plus
 * DataForm-Schnellbearbeitung im Modal. Felder aus dem PHP-Schema «projekt»,
 * Speichern per REST (/wp/v2/projekte/<id>) – dieselbe Säuberung und
 * Rechteprüfung wie im Editor.
 */
import { createRoot, useEffect, useMemo, useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Button, Modal, Notice, Spinner } from '@wordpress/components';
import { DataViews, DataForm, filterSortAndPaginate, useFormValidity } from '@wordpress/dataviews';
import { toDataFormFields } from './schema-to-dataform';
import './projekte-admin.scss';

// Medien-, Verlaufs- und Listenzellen kommen aus der ungebündelten Maske
// (assets/js/schema-form.js, Abhängigkeit in admin.php). Ausserhalb des
// Block-Editors braucht MediaUpload den Filter, den sonst der Editor setzt.
import { addFilter } from '@wordpress/hooks';
import { MediaUpload } from '@wordpress/media-utils';
addFilter( 'editor.MediaUpload', 'konstrukta/media-upload', () => MediaUpload );

const schema = window.kxEditor.schemas.projekt;
const gradients = window.kxEditor.gradients;

const toItem = ( post ) => {
	const item = { id: post.id, title: post.title.raw ?? post.title.rendered, status: post.status, link: post.link, edit: `post.php?post=${ post.id }&action=edit`, thumb: post._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ]?.source_url };
	schema.fields.forEach( ( f ) => ( item[ f.key ] = post.meta[ schema.prefix + f.key ] ) );
	return item;
};

function App() {
	const [ posts, setPosts ] = useState( null );
	const [ media, setMedia ] = useState( {} );
	const [ editing, setEditing ] = useState( null );
	const [ error, setError ] = useState( null );
	const [ view, setView ] = useState( {
		type: 'table',
		perPage: 10,
		page: 1,
		sort: { field: 'jahr', direction: 'desc' },
		search: '',
		filters: [],
		titleField: 'title',
		mediaField: 'ansicht',
		descriptionField: 'kunde',
		fields: [ 'branche', 'jahr', 'standort', 'stimmung', 'hervorheben' ],
		layout: {},
	} );

	const load = () =>
		apiFetch( { path: '/wp/v2/projekte?context=edit&per_page=100&status=publish,draft' } )
			.then( ( list ) => {
				setPosts( list.map( toItem ) );
				const ids = [ ...new Set( list.map( ( p ) => p.meta.kx_ansicht ).filter( Boolean ) ) ];
				if ( ids.length ) {
					apiFetch( { path: `/wp/v2/media?include=${ ids.join( ',' ) }&per_page=100&_fields=id,source_url,media_details` } ).then( ( m ) => {
						const map = {};
						m.forEach( ( x ) => ( map[ x.id ] = x.media_details?.sizes?.medium?.source_url || x.source_url ) );
						setMedia( map );
					} );
				}
			} )
			.catch( ( e ) => setError( e.message ) );
	useEffect( () => {
		load();
	}, [] );

	const fields = useMemo( () => {
		const base = toDataFormFields( schema ).map( ( f ) => {
			if ( f.id === 'ansicht' ) {
				return { ...f, render: ( { item } ) => ( media[ item.ansicht ] ? <img src={ media[ item.ansicht ] } alt="" style={ { width: '100%', height: '100%', objectFit: 'cover' } } /> : null ), enableSorting: false };
			}
			if ( f.id === 'stimmung' ) {
				return { ...f, render: ( { item } ) => <span className="kx-swatch" style={ { background: gradients.find( ( g ) => g.value === item.stimmung )?.gradient || item.stimmung } } title={ gradients.find( ( g ) => g.value === item.stimmung )?.name } />, enableSorting: false };
			}
			if ( f.id === 'branche' ) {
				return { ...f, filterBy: { operators: [ 'isAny' ] } };
			}
			if ( f.id === 'hervorheben' ) {
				return { ...f, render: ( { item } ) => ( item.hervorheben ? 'Ja' : '–' ), elements: [ { value: true, label: 'Ja' }, { value: false, label: 'Nein' } ], filterBy: { operators: [ 'is' ] } };
			}
			return f;
		} );
		return [ { id: 'title', label: 'Titel', type: 'text', enableGlobalSearch: true, isValid: { required: true } }, ...base ];
	}, [ media ] );

	const { data, paginationInfo } = useMemo( () => filterSortAndPaginate( posts || [], view, fields ), [ posts, view, fields ] );

	const actions = [
		{ id: 'quick-edit', label: 'Schnellbearbeitung', isPrimary: true, callback: ( [ item ] ) => setEditing( item ) },
		{ id: 'edit', label: 'Im Editor öffnen', callback: ( [ item ] ) => ( window.location.href = item.edit ) },
		{ id: 'view', label: 'Ansehen', callback: ( [ item ] ) => window.open( item.link, '_blank' ) },
	];

	if ( error ) {
		return <Notice status="error" isDismissible={ false }>{ error }</Notice>;
	}
	if ( ! posts ) {
		return <Spinner />;
	}
	return (
		<>
			<DataViews
				data={ data }
				fields={ fields }
				view={ view }
				onChangeView={ setView }
				actions={ actions }
				paginationInfo={ paginationInfo }
				defaultLayouts={ { table: {}, grid: {}, list: {} } }
				getItemId={ ( item ) => String( item.id ) }
			/>
			{ editing && <QuickEdit item={ editing } fields={ fields } onClose={ () => setEditing( null ) } onSaved={ () => { setEditing( null ); load(); } } /> }
		</>
	);
}

function QuickEdit( { item, fields, onClose, onSaved } ) {
	const [ draft, setDraft ] = useState( item );
	const [ saving, setSaving ] = useState( false );
	const [ error, setError ] = useState( null );
	const form = {
		layout: { type: 'card' },
		fields: [
			{ id: 'basis', label: 'Grunddaten', layout: { type: 'card', isOpened: true }, children: [ 'title', { id: 'r1', layout: { type: 'row' }, children: [ 'kunde', 'branche', 'jahr' ] }, 'standort', 'kurzbeschrieb' ] },
			{ id: 'bild', label: 'Bild & Stimmung', layout: { type: 'card', isOpened: true }, children: [ 'ansicht', 'stimmung', 'hervorheben' ] },
			{ id: 'listen', label: 'Listen', layout: { type: 'card', isOpened: false }, children: [ 'kennzahlen', 'leistungen' ] },
		],
	};
	const { validity, isValid } = useFormValidity( draft, fields, form );

	const save = () => {
		setSaving( true );
		const meta = {};
		schema.fields.forEach( ( f ) => ( meta[ schema.prefix + f.key ] = draft[ f.key ] ) );
		apiFetch( { path: `/wp/v2/projekte/${ item.id }`, method: 'POST', data: { title: draft.title, meta } } )
			.then( onSaved )
			.catch( ( e ) => {
				setError( e.message );
				setSaving( false );
			} );
	};

	return (
		<Modal title={ `Schnellbearbeitung: ${ item.title }` } onRequestClose={ onClose } size="large">
			<div className="kx-modal-form">
				{ error && <Notice status="error" isDismissible={ false }>{ error }</Notice> }
				<DataForm data={ draft } fields={ fields } form={ form } validity={ validity } onChange={ ( edits ) => setDraft( ( d ) => ( { ...d, ...edits } ) ) } />
				<div className="kx-modal-actions">
					<Button variant="tertiary" onClick={ onClose }>Abbrechen</Button>
					<Button variant="primary" onClick={ save } isBusy={ saving } disabled={ ! isValid || saving } accessibleWhenDisabled>
						Speichern
					</Button>
				</div>
			</div>
		</Modal>
	);
}

createRoot( document.getElementById( 'kx-projekte-root' ) ).render( <App /> );
