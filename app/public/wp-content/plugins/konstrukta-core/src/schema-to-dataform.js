/**
 * Übersetzt das PHP-Schema (window.kxEditor.schemas) in DataForm-Felder.
 * Medien, Verläufe und Listen verwenden die Komponenten der ungebündelten
 * Maske (window.kxSchemaForm) als eigenes Edit – ein Satz Feldtypen.
 */
import { BaseControl, TextControl } from '@wordpress/components';

const SF = () => window.kxSchemaForm || {};

const isEmpty = ( v ) => v === undefined || v === null || v === '' || ( Array.isArray( v ) && ! v.length );

function wrap( Inner ) {
	return function Edit( { data, field, onChange, hideLabelFromVision } ) {
		return (
			<BaseControl __nextHasNoMarginBottom label={ hideLabelFromVision ? undefined : field.label } help={ field.description }>
				<Inner value={ data[ field.id ] } onChange={ ( v ) => onChange( { [ field.id ]: v } ) } field={ field } />
			</BaseControl>
		);
	};
}

// Eigenes Datumsfeld: Das DataForm-Datum bietet nur Vergangenheits-Vorgaben
// («Today», «Past week» …, unübersetzt) – für ein Prüfdatum in der Zukunft unpassend.
function DateEdit( { data, field, onChange, hideLabelFromVision } ) {
	return (
		<TextControl
			__nextHasNoMarginBottom
			__next40pxDefaultSize
			type="date"
			label={ hideLabelFromVision ? undefined : field.label }
			help={ field.description }
			value={ data[ field.id ] || '' }
			onChange={ ( v ) => onChange( { [ field.id ]: v } ) }
		/>
	);
}

const MediaEdit = wrap( ( { value, onChange, field } ) => {
	const { MediaCell } = SF();
	return MediaCell ? <MediaCell value={ value } onChange={ onChange } label={ field.label } /> : null;
} );
const GradientEdit = wrap( ( { value, onChange, field } ) => {
	const { GradientCell } = SF();
	return GradientCell ? <GradientCell value={ value } onChange={ onChange } label={ field.label } /> : null;
} );
const rowsEdit = ( schemaField ) =>
	wrap( ( { value, onChange } ) => {
		const { RowEditor } = SF();
		return RowEditor ? <RowEditor field={ schemaField } rows={ value } onChange={ onChange } /> : null;
	} );

export function toDataFormFields( schema ) {
	return schema.fields.map( ( f ) => {
		const base = {
			id: f.key,
			label: f.label,
			description: f.help,
			isValid: {},
		};
		if ( f.required ) {
			base.isValid.required = true;
		}
		if ( f.maxLength ) {
			base.isValid.maxLength = f.maxLength;
		}
		switch ( f.type || 'text' ) {
			case 'textarea':
				return { ...base, type: 'text', Edit: { control: 'textarea', rows: 3 } };
			case 'number':
				return { ...base, type: 'integer', render: ( { item } ) => ( item[ f.key ] ? String( item[ f.key ] ) : '–' ), isValid: { ...base.isValid, min: f.min, max: f.max } };
			case 'select':
				return { ...base, type: 'text', elements: f.options, Edit: 'select' };
			case 'toggle':
				return { ...base, type: 'boolean', Edit: 'toggle' };
			case 'user':
				return {
					...base,
					type: 'integer',
					elements: [ { value: 0, label: '— niemand —' }, ...( window.kxEditor?.users || [] ).map( ( u ) => ( { value: u.value, label: u.label } ) ) ],
					Edit: 'select',
				};
			case 'email':
				return { ...base, type: 'email' };
			case 'url':
				return {
					...base,
					type: 'text',
					Edit: 'url',
					isValid: {
						...base.isValid,
						custom: ( item ) => ( item[ f.key ] && ! /^(https?:\/\/|\/)/.test( item[ f.key ] ) ? 'Muss mit https:// oder / beginnen.' : null ),
					},
				};
			case 'date':
				return {
					...base,
					type: 'text',
					Edit: DateEdit,
					render: ( { item } ) => ( item[ f.key ] ? item[ f.key ].split( '-' ).reverse().join( '.' ) : '–' ),
				};
			case 'media':
				return {
					...base,
					type: 'integer',
					Edit: MediaEdit,
					render: ( { item } ) => ( item[ f.key ] ? 'Bild #' + item[ f.key ] : '–' ),
					isValid: f.required ? { custom: ( item ) => ( item[ f.key ] ? null : 'Bitte ein Bild wählen.' ) } : {},
				};
			case 'gradient':
				return { ...base, type: 'text', Edit: GradientEdit };
			case 'rows':
				return {
					...base,
					Edit: rowsEdit( f ),
					render: ( { item } ) => ( item[ f.key ] || [] ).length + ' Einträge',
					enableSorting: false,
				};
			default:
				return { ...base, type: 'text' };
		}
	} );
}

export function emptyRequired( schema, item ) {
	return schema.fields.filter( ( f ) => f.required && isEmpty( item[ f.key ] ) );
}
