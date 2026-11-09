/**
 * DataForm im Seiten-Editor – an zwei Stellen:
 *
 *  1. Oben in der Zusammenfassung (PluginPostStatusInfo), neben Status und
 *     Template: die Felder mit group «zuordnung» (Rubrik, Ansprechperson,
 *     Prüfen bis) als Zeilen im Panel-Layout.
 *  2. Panel «Seitenkopf & Teaser»: die übrigen Felder.
 *
 * Felder kommen aus dem PHP-Schema «seite». Gespeichert wird in Post-Meta
 * über editPost – mit dem Beitrag, mit Rückgängig und Revisionen.
 * kx_claim/kx_teaser sind zugleich per Block-Binding im Seitenkopf editierbar.
 */
import { registerPlugin } from '@wordpress/plugins';
import { PluginDocumentSettingPanel, PluginPostStatusInfo } from '@wordpress/editor';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useMemo } from '@wordpress/element';
import { Notice } from '@wordpress/components';
import { DataForm, useFormValidity } from '@wordpress/dataviews';
import { toDataFormFields } from './schema-to-dataform';
import './dataform.scss';

const schema = window.kxEditor?.schemas?.seite;
const users = window.kxEditor?.users || [];

/** Werte, Schreiben, Validierung und Speichersperre für eine Feldgruppe. */
function useSeitenForm( fieldIds, lockName ) {
	const meta = useSelect( ( s ) => s( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}, [] );
	const postType = useSelect( ( s ) => s( 'core/editor' ).getCurrentPostType(), [] );
	const { editPost, lockPostSaving, unlockPostSaving } = useDispatch( 'core/editor' );
	const fields = useMemo( () => toDataFormFields( schema ).filter( ( f ) => fieldIds.includes( f.id ) ), [] );

	const item = {};
	schema.fields.forEach( ( f ) => {
		const v = meta[ schema.prefix + f.key ];
		item[ f.key ] = v === undefined || v === null ? f.default ?? '' : v;
	} );

	const onChange = ( edits ) => {
		const metaEdits = {};
		Object.keys( edits ).forEach( ( k ) => ( metaEdits[ schema.prefix + k ] = edits[ k ] ?? '' ) );
		editPost( { meta: metaEdits } );
	};

	return { item, fields, onChange, postType, lockPostSaving, unlockPostSaving, lockName };
}

function useLock( isValid, { lockPostSaving, unlockPostSaving, lockName } ) {
	useEffect( () => {
		if ( isValid ) {
			unlockPostSaving( lockName );
		} else {
			lockPostSaving( lockName );
		}
		return () => unlockPostSaving( lockName );
	}, [ isValid ] );
}

const groupIds = schema ? schema.fields.filter( ( f ) => f.group === 'zuordnung' ).map( ( f ) => f.key ) : [];
const restIds = schema ? schema.fields.filter( ( f ) => f.group !== 'zuordnung' ).map( ( f ) => f.key ) : [];

/* 1. Zuordnung oben in der Zusammenfassung ------------------------------- */
function Zuordnung() {
	const ctx = useSeitenForm( groupIds, 'kx-seite-zuordnung' );
	const form = useMemo( () => ( { layout: { type: 'panel', labelPosition: 'side' }, fields: groupIds } ), [] );
	const { validity, isValid } = useFormValidity( ctx.item, ctx.fields, form );
	useLock( isValid, ctx );
	if ( ctx.postType !== 'page' ) {
		return null;
	}
	const overdue = ctx.item.pruefen_bis && ctx.item.pruefen_bis < new Date().toISOString().slice( 0, 10 );
	const person = users.find( ( u ) => u.value === ctx.item.ansprechperson );
	return (
		<PluginPostStatusInfo className="kx-zuordnung">
			<div className="kx-zuordnung__inner">
				<DataForm data={ ctx.item } fields={ ctx.fields } form={ form } validity={ validity } onChange={ ctx.onChange } />
				{ overdue && (
					<Notice status="error" isDismissible={ false }>
						Prüfdatum überschritten – bitte Inhalt prüfen{ person ? ` (${ person.label })` : '' }.
					</Notice>
				) }
			</div>
		</PluginPostStatusInfo>
	);
}

/* 2. Seitenkopf & Teaser -------------------------------------------------- */
function SeitenPanel() {
	const ctx = useSeitenForm( restIds, 'kx-seite' );
	const form = useMemo( () => ( { layout: { type: 'regular', labelPosition: 'top' }, fields: restIds } ), [] );
	const { validity, isValid } = useFormValidity( ctx.item, ctx.fields, form );
	useLock( isValid, ctx );
	if ( ctx.postType !== 'page' ) {
		return null;
	}
	return (
		<PluginDocumentSettingPanel name="kx-seite" title={ schema.title + ' (DataForm)' } className="kx-dataform-panel">
			{ ! isValid && (
				<Notice status="warning" isDismissible={ false }>
					Bitte die markierten Felder prüfen – Speichern ist gesperrt.
				</Notice>
			) }
			<DataForm data={ ctx.item } fields={ ctx.fields } form={ form } validity={ validity } onChange={ ctx.onChange } />
		</PluginDocumentSettingPanel>
	);
}

if ( schema ) {
	registerPlugin( 'kx-seite-zuordnung', { render: Zuordnung } );
	registerPlugin( 'kx-seite-dataform', { render: SeitenPanel } );
}
