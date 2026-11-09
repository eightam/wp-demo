/**
 * DataForm in der Seitenleiste des Seiten-Editors.
 *
 * Felder kommen aus dem PHP-Schema «seite». Gespeichert wird in Post-Meta
 * über editPost – also mit dem Beitrag, mit Rückgängig und Revisionen.
 * kx_claim/kx_teaser sind zugleich per Block-Binding im Seitenkopf editierbar:
 * tippt man auf der Leinwand, ändert sich das Formular mit.
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel } from '@wordpress/editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useMemo } from '@wordpress/element';
import { Notice } from '@wordpress/components';
import { DataForm, useFormValidity } from '@wordpress/dataviews';
import { toDataFormFields } from './schema-to-dataform';
import './dataform.scss';

const schema = window.kxEditor?.schemas?.seite;

function SeitenPanel() {
	const meta = useSelect( ( s ) => s( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}, [] );
	const postType = useSelect( ( s ) => s( 'core/editor' ).getCurrentPostType(), [] );
	const { editPost, lockPostSaving, unlockPostSaving } = useDispatch( 'core/editor' );

	const fields = useMemo( () => toDataFormFields( schema ), [] );
	const form = useMemo(
		() => ( {
			layout: { type: 'regular', labelPosition: 'top' },
			fields: [
				'claim',
				'teaser',
				{ id: 'zuordnung', label: 'Zuordnung', layout: { type: 'panel', labelPosition: 'side' }, children: [ 'rubrik', 'kontakt_email', 'pruefen_bis' ] },
				'cta_url',
				'im_footer',
			],
		} ),
		[]
	);

	const item = {};
	schema.fields.forEach( ( f ) => {
		const v = meta[ schema.prefix + f.key ];
		item[ f.key ] = v === undefined || v === null ? f.default ?? '' : v;
	} );

	const { validity, isValid } = useFormValidity( item, fields, form );

	useEffect( () => {
		if ( isValid ) {
			unlockPostSaving( 'kx-seite' );
		} else {
			lockPostSaving( 'kx-seite' );
		}
		return () => unlockPostSaving( 'kx-seite' );
	}, [ isValid ] );

	if ( postType !== 'page' ) {
		return null;
	}

	const onChange = ( edits ) => {
		const metaEdits = {};
		Object.keys( edits ).forEach( ( k ) => ( metaEdits[ schema.prefix + k ] = edits[ k ] ?? '' ) );
		editPost( { meta: metaEdits } );
	};

	return (
		<PluginDocumentSettingPanel name="kx-seite" title={ schema.title + ' (DataForm)' } className="kx-dataform-panel">
			{ item.pruefen_bis && item.pruefen_bis < new Date().toISOString().slice( 0, 10 ) && (
				<Notice status="error" isDismissible={ false }>
					Prüfdatum überschritten – bitte Inhalt prüfen
					{ item.kontakt_email ? ` (Ansprechperson: ${ item.kontakt_email })` : '' }.
				</Notice>
			) }
			{ ! isValid && (
				<Notice status="warning" isDismissible={ false }>
					Bitte die markierten Felder prüfen – Speichern ist gesperrt.
				</Notice>
			) }
			<DataForm data={ item } fields={ fields } form={ form } validity={ validity } onChange={ onChange } />
		</PluginDocumentSettingPanel>
	);
}

if ( schema ) {
	registerPlugin( 'kx-seite-dataform', { render: SeitenPanel } );
}
