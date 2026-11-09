/**
 * Konstrukta – schemagesteuerte Pflegemaske. Ohne Build-Schritt.
 *
 * Das Schema kommt aus PHP (includes/schema.php) über window.kxEditor.
 * Dieselben Komponenten zeichnen
 *   - die Maske eines Referenzprojekts (Daten in Post-Meta, editPost)
 *   - die Inhaltsblöcke «Kennzahlen» und «Standorte» (Daten als Attribute, setAttributes)
 *   - die Einstellungen in der Seitenleiste (Field = früher control())
 *
 * Inhalt:
 *   Field       ein Einzelwert, alle Typen (Leinwand und Seitenleiste)
 *   MediaCell   Bildwahl aus der Mediathek
 *   GradientCell Verlauf aus theme.json oder eigener Wert
 *   RowEditor   eine Liste mit Spalten, Zeilen verschieben/entfernen
 *   SchemaForm  setzt Felder und Listen nach Schema zusammen
 *   Stores      useMetaStore (editPost) · useAttributeStore (setAttributes)
 */
( function ( wp ) {
	const { createElement: el, Fragment, useEffect, useState } = wp.element;
	const { registerBlockType, registerBlockBindingsSource } = wp.blocks;
	const { useBlockProps, InspectorControls, BlockControls, MediaUpload } = wp.blockEditor;
	const {
		PanelBody, TextControl, TextareaControl, SelectControl, ToggleControl,
		Button, Notice, ToolbarGroup, ToolbarButton, BaseControl, Placeholder,
	} = wp.components;
	const { useSelect, useDispatch } = wp.data;
	const { SVG, Path } = wp.primitives;
	const SSR = typeof wp.serverSideRender === 'function' ? wp.serverSideRender : wp.serverSideRender.ServerSideRender || wp.serverSideRender.default;

	const KX = window.kxEditor || { schemas: {}, gradients: [], firma: {}, firmaFields: {} };

	/* ------------------------------------------------------------------
	 * Symbole als Inline-SVG: die Leinwand ist ein iframe ohne Dashicons.
	 * ---------------------------------------------------------------- */
	const icon = ( d ) => el( SVG, { xmlns: 'http://www.w3.org/2000/svg', viewBox: '0 0 24 24', width: 20, height: 20 }, el( Path, { d } ) );
	const ICONS = {
		up: icon( 'M12 7.5 6.5 13l1.06 1.06L12 9.62l4.44 4.44L17.5 13z' ),
		down: icon( 'M12 16.5 6.5 11l1.06-1.06L12 14.38l4.44-4.44L17.5 11z' ),
		trash: icon( 'M9 4h6v1.5h4V7H5V5.5h4zm-2.5 4h11l-.8 11.2a1 1 0 0 1-1 .8H8.3a1 1 0 0 1-1-.8zM10 10v8h1.5v-8zm2.5 0v8H14v-8z' ),
		plus: icon( 'M11.25 5h1.5v6.25H19v1.5h-6.25V19h-1.5v-6.25H5v-1.5h6.25z' ),
		eye: icon( 'M12 6c-4.4 0-7.7 3.3-9 6 1.3 2.7 4.6 6 9 6s7.7-3.3 9-6c-1.3-2.7-4.6-6-9-6zm0 10.5A4.5 4.5 0 1 1 12 7.5a4.5 4.5 0 0 1 0 9zm0-7a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z' ),
		pencil: icon( 'M15.6 4.4a2 2 0 0 1 2.8 0l1.2 1.2a2 2 0 0 1 0 2.8L9 19H5v-4zM14.5 7.6 7 15.1V17h1.9l7.5-7.5z' ),
		image: icon( 'M5 5h14v14H5zm1.5 1.5v8.4l3-3 3 3 2-2 3 3V6.5zM15 8a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3z' ),
	};

	const common = { __nextHasNoMarginBottom: true, __next40pxDefaultSize: true };

	/** Leer im Sinne von «Pflichtfeld nicht ausgefüllt». */
	const isEmpty = ( v ) => v === undefined || v === null || v === '' || v === 0 || ( Array.isArray( v ) && ! v.length );

	/** Jede Zeile auf genau die deklarierten Spalten bringen, alles als Zeichenkette. */
	const clean = ( field, rows ) =>
		( rows || [] ).map( ( row ) => {
			const out = {};
			field.columns.forEach( ( c ) => {
				const v = row && row[ c.key ];
				out[ c.key ] = v === undefined || v === null ? '' : String( v );
			} );
			return out;
		} );

	/* ------------------------------------------------------------------
	 * MediaCell
	 * ---------------------------------------------------------------- */
	function MediaCell( { value, onChange, asString, label } ) {
		const id = parseInt( value, 10 ) || 0;
		const media = useSelect( ( s ) => ( id ? s( 'core' ).getEntityRecord( 'postType', 'attachment', id, { context: 'view' } ) : null ), [ id ] );
		const src = media && ( ( media.media_details && media.media_details.sizes && media.media_details.sizes.thumbnail && media.media_details.sizes.thumbnail.source_url ) || media.source_url );
		const set = ( v ) => onChange( asString ? String( v || '' ) : v || 0 );
		// canUser statt MediaUploadCheck: funktioniert auch ausserhalb des Block-Editors.
		const canUpload = useSelect( ( s ) => s( 'core' ).canUser( 'create', { kind: 'postType', name: 'attachment' } ), [] );
		return el( 'div', { className: 'kx-media' },
			el( 'div', { className: 'kx-media__thumb' }, src ? el( 'img', { src, alt: '' } ) : ICONS.image ),
			canUpload
				? el( MediaUpload, {
					allowedTypes: [ 'image' ],
					value: id,
					title: label || 'Bild wählen',
					onSelect: ( m ) => set( m.id ),
					render: ( { open } ) => el( Button, { variant: 'secondary', size: 'small', onClick: open }, id ? 'Ersetzen' : 'Mediathek' ),
				} )
				: el( 'span', null, id ? '#' + id : '–' ),
			id ? el( Button, { variant: 'link', isDestructive: true, size: 'small', onClick: () => set( 0 ) }, 'Entfernen' ) : null
		);
	}

	/* ------------------------------------------------------------------
	 * GradientCell – Verläufe aus theme.json; Fremdwert bleibt wählbar.
	 * ---------------------------------------------------------------- */
	function GradientCell( { value, onChange, label, hideLabel } ) {
		const known = KX.gradients.find( ( g ) => g.value === value );
		const options = [ { value: '', label: '— keiner —' } ].concat( KX.gradients.map( ( g ) => ( { value: g.value, label: g.name } ) ) );
		if ( value && ! known ) {
			options.push( { value, label: 'Eigener Wert' } );
		}
		const preview = known ? known.gradient : value;
		return el( 'div', { className: 'kx-gradient' },
			el( 'span', { className: 'kx-gradient__swatch', style: { background: preview || 'repeating-conic-gradient(#ddd 0 25%, #fff 0 50%) 0 0/10px 10px' } } ),
			el( SelectControl, Object.assign( {}, common, { label: label || 'Verlauf', hideLabelFromVision: hideLabel !== false, value: value || '', options, onChange } ) )
		);
	}

	/* ------------------------------------------------------------------
	 * Field – ein Einzelwert. Ersetzt control() (Seitenleiste) und field()
	 * (Maske); beide waren fast gleich.
	 * ---------------------------------------------------------------- */
	function Field( { field, value, onChange, missing } ) {
		const label = field.label + ( field.required ? ' *' : '' );
		const help = missing ? 'Pflichtfeld.' : field.help;
		switch ( field.type || 'text' ) {
			case 'textarea':
				return el( TextareaControl, Object.assign( {}, common, { label, help, value: value || '', rows: 3, onChange } ) );
			case 'number':
				return el( TextControl, Object.assign( {}, common, {
					label, help, type: 'number', min: field.min, max: field.max,
					value: value || value === 0 ? String( value ) : '',
					onChange: ( v ) => onChange( v === '' ? 0 : parseInt( v, 10 ) || 0 ),
				} ) );
			case 'select':
				return el( SelectControl, Object.assign( {}, common, { label, help, value: value || field.default || '', options: field.options, onChange } ) );
			case 'toggle':
				return el( ToggleControl, { __nextHasNoMarginBottom: true, label, help, checked: !! value, onChange } );
			case 'user':
				return el( SelectControl, Object.assign( {}, common, {
					label, help, value: String( value || 0 ),
					options: [ { value: '0', label: '— niemand —' } ].concat( ( KX.users || [] ).map( ( u ) => ( { value: String( u.value ), label: u.label } ) ) ),
					onChange: ( v ) => onChange( parseInt( v, 10 ) || 0 ),
				} ) );
			case 'media':
				return el( BaseControl, { __nextHasNoMarginBottom: true, label, help, id: 'kx-' + field.key },
					el( MediaCell, { value, onChange, label: field.label } ) );
			case 'gradient':
				return el( BaseControl, { __nextHasNoMarginBottom: true, label, help, id: 'kx-' + field.key },
					el( GradientCell, { value, onChange, label: field.label } ) );
			case 'url':
			case 'email':
			case 'date':
				return el( TextControl, Object.assign( {}, common, { label, help, type: field.type, value: value || '', onChange } ) );
			default:
				return el( TextControl, Object.assign( {}, common, { label, help, value: value || '', onChange } ) );
		}
	}

	/* ------------------------------------------------------------------
	 * RowEditor – eine Liste.
	 * ---------------------------------------------------------------- */
	function RowEditor( { field, rows, onChange } ) {
		const list = clean( field, rows );
		const grid = field.columns.map( ( c ) => ( c.weight || 1 ) + 'fr' ).join( ' ' ) + ' 104px';
		const write = ( next ) => onChange( clean( field, next ) );
		const setCell = ( i, key, v ) => write( list.map( ( r, j ) => ( j === i ? Object.assign( {}, r, { [ key ]: v } ) : r ) ) );
		const move = ( i, d ) => {
			const next = list.slice();
			const [ row ] = next.splice( i, 1 );
			next.splice( i + d, 0, row );
			write( next );
		};
		const cell = ( col, row, i ) => {
			const v = row[ col.key ];
			if ( col.type === 'media' ) {
				return el( MediaCell, { value: v, asString: true, onChange: ( x ) => setCell( i, col.key, x ), label: col.label } );
			}
			if ( col.type === 'gradient' ) {
				return el( GradientCell, { value: v, onChange: ( x ) => setCell( i, col.key, x ), label: col.label } );
			}
			return el( TextControl, Object.assign( {}, common, {
				label: col.label, hideLabelFromVision: true, placeholder: col.placeholder, value: v,
				onChange: ( x ) => setCell( i, col.key, x ),
			} ) );
		};

		return el( 'div', { className: 'kx-rows' },
			field.help ? el( 'p', { className: 'kx-rows__help' }, field.help ) : null,
			list.length
				? el( Fragment, null,
					el( 'div', { className: 'kx-rows__head', style: { gridTemplateColumns: grid } },
						field.columns.map( ( c ) => el( 'span', { key: c.key }, c.label ) ), el( 'span' ) ),
					list.map( ( row, i ) =>
						el( 'div', { key: i, className: 'kx-rows__row', style: { gridTemplateColumns: grid } },
							field.columns.map( ( c ) => el( 'div', { key: c.key, className: 'kx-rows__cell' }, cell( c, row, i ) ) ),
							el( 'div', { className: 'kx-rows__actions' },
								el( Button, { icon: ICONS.up, label: 'Nach oben', size: 'small', disabled: i === 0, onClick: () => move( i, -1 ) } ),
								el( Button, { icon: ICONS.down, label: 'Nach unten', size: 'small', disabled: i === list.length - 1, onClick: () => move( i, 1 ) } ),
								el( Button, { icon: ICONS.trash, label: 'Entfernen', size: 'small', isDestructive: true, onClick: () => write( list.filter( ( _, j ) => j !== i ) ) } )
							)
						)
					) )
				: el( 'p', { className: 'kx-rows__empty' }, field.empty || 'Noch keine Einträge.' ),
			el( Button, {
				variant: 'secondary', icon: ICONS.plus, className: 'kx-rows__add',
				onClick: () => write( list.concat( [ {} ] ) ),
			}, field.add || 'Hinzufügen' )
		);
	}

	/* ------------------------------------------------------------------
	 * SchemaForm – Grunddaten zweispaltig, jede Liste ein Panel.
	 * ---------------------------------------------------------------- */
	function SchemaForm( { schema, values, set, missing } ) {
		const scalars = schema.fields.filter( ( f ) => f.type !== 'rows' && f.placement !== 'sidebar' );
		const tables = schema.fields.filter( ( f ) => f.type === 'rows' );
		const miss = missing || [];
		return el( 'div', { className: 'kx-form' },
			el( 'div', { className: 'kx-form__title' },
				el( 'strong', null, schema.title ),
				el( 'span', null, schema.storage === 'meta' ? 'gespeichert mit dem Beitrag' : 'gespeichert im Block' )
			),
			miss.length
				? el( Notice, { status: 'warning', isDismissible: false, className: 'kx-form__notice' },
					'Pflichtfelder fehlen: ' + miss.map( ( f ) => f.label ).join( ', ' ) + '. Speichern und Veröffentlichen sind gesperrt.' )
				: null,
			scalars.length
				? el( PanelBody, { title: 'Grunddaten', initialOpen: true },
					el( 'div', { className: 'kx-form__grid' },
						scalars.map( ( f ) => el( 'div', { key: f.key, className: 'kx-form__field' + ( f.wide ? ' is-wide' : '' ) + ( miss.indexOf( f ) > -1 ? ' is-missing' : '' ) },
							el( Field, { field: f, value: values[ f.key ], onChange: ( v ) => set( f.key, v ), missing: miss.indexOf( f ) > -1 } ) ) )
					) )
				: null,
			tables.map( ( f, i ) =>
				el( PanelBody, {
					key: f.key,
					title: f.label + ' (' + ( values[ f.key ] || [] ).length + ')',
					initialOpen: ! scalars.length && i === 0,
				}, el( RowEditor, { field: f, rows: values[ f.key ], onChange: ( v ) => set( f.key, v ) } ) )
			)
		);
	}

	/** Seitenleisten-Felder eines Schemas (placement: sidebar). */
	function SidebarFields( { schema, values, set } ) {
		const fields = schema.fields.filter( ( f ) => f.placement === 'sidebar' );
		if ( ! fields.length ) {
			return null;
		}
		return el( InspectorControls, null,
			el( PanelBody, { title: 'Einstellungen' },
				fields.map( ( f ) => el( 'div', { key: f.key, style: { marginBottom: 16 } },
					el( Field, { field: f, value: values[ f.key ], onChange: ( v ) => set( f.key, v ) } ) ) ) ) );
	}

	/* ------------------------------------------------------------------
	 * Stores: wo die Werte liegen.
	 * ---------------------------------------------------------------- */
	function useMetaStore( schema ) {
		const meta = useSelect( ( s ) => s( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {}, [] );
		const { editPost } = useDispatch( 'core/editor' );
		const values = {};
		schema.fields.forEach( ( f ) => {
			const v = meta[ schema.prefix + f.key ];
			values[ f.key ] = v === undefined ? f.default : v;
		} );
		const set = ( key, v ) => editPost( { meta: { [ schema.prefix + key ]: v } } );
		return [ values, set ];
	}

	function useAttributeStore( schema, attributes, setAttributes ) {
		const values = {};
		schema.fields.forEach( ( f ) => ( values[ f.key ] = attributes[ f.key ] === undefined ? f.default : attributes[ f.key ] ) );
		return [ values, ( key, v ) => setAttributes( { [ key ]: v } ) ];
	}

	/** Pflichtfelder: Speichern sperren, solange etwas fehlt. */
	function useRequired( schema, values, lockName ) {
		const missing = schema.fields.filter( ( f ) => f.required && isEmpty( values[ f.key ] ) );
		const { lockPostSaving, unlockPostSaving } = useDispatch( 'core/editor' );
		const key = missing.map( ( f ) => f.key ).join( ',' );
		useEffect( () => {
			if ( missing.length ) {
				lockPostSaving( lockName );
			} else {
				unlockPostSaving( lockName );
			}
			return () => unlockPostSaving( lockName );
		}, [ key ] );
		return missing;
	}

	/* ------------------------------------------------------------------
	 * Block: Projektdaten (Meta). Steht im gesperrten Template von kx_projekt.
	 * ---------------------------------------------------------------- */
	function MetaForm( { schema } ) {
		const [ values, set ] = useMetaStore( schema );
		const missing = useRequired( schema, values, 'kx-required-' + schema.post_type );
		return el( SchemaForm, { schema, values, set, missing } );
	}

	registerBlockType( 'konstrukta/projekt-daten', {
		apiVersion: 3,
		edit: function () {
			const postType = useSelect( ( s ) => s( 'core/editor' ).getCurrentPostType(), [] );
			const blockProps = useBlockProps( { className: 'kx-form-block' } );
			const schema = KX.schemas.projekt;
			if ( postType !== schema.post_type ) {
				return el( 'div', blockProps, el( Notice, { status: 'info', isDismissible: false }, 'Die Projektdaten-Maske funktioniert nur in einem Referenzprojekt.' ) );
			}
			return el( 'div', blockProps, el( MetaForm, { schema } ) );
		},
	} );

	/* ------------------------------------------------------------------
	 * Inhaltsblöcke (Attribute): Maske bei Auswahl, Server-Vorschau sonst –
	 * der Bearbeiten/Vorschau-Wechsel von ACF, ohne ACF.
	 * ---------------------------------------------------------------- */
	Object.keys( KX.schemas ).forEach( ( id ) => {
		const schema = KX.schemas[ id ];
		if ( schema.storage !== 'attributes' ) {
			return;
		}
		registerBlockType( schema.block, {
			apiVersion: 3,
			edit: function ( { attributes, setAttributes, isSelected } ) {
				const [ preview, setPreview ] = useState( false );
				const [ values, set ] = useAttributeStore( schema, attributes, setAttributes );
				const blockProps = useBlockProps( { className: isSelected && ! preview ? 'kx-form-block' : 'kx-preview-block' } );
				const showForm = isSelected && ! preview;
				return el( Fragment, null,
					el( BlockControls, null,
						el( ToolbarGroup, null,
							el( ToolbarButton, {
								icon: preview ? ICONS.pencil : ICONS.eye,
								label: preview ? 'Bearbeiten' : 'Vorschau',
								onClick: () => setPreview( ! preview ),
							} ) ) ),
					el( SidebarFields, { schema, values, set } ),
					el( 'div', blockProps,
						showForm
							? el( SchemaForm, { schema, values, set } )
							: el( SSR, { block: schema.block, attributes, httpMethod: 'POST', skipBlockSupportAttributes: true } ) )
				);
			},
		} );
	} );

	/* ------------------------------------------------------------------
	 * Projekt-Ansicht (Template-Teil) und Projektkarten: Server-Vorschau,
	 * Einstellungen in der Seitenleiste – wieder mit Field.
	 * ---------------------------------------------------------------- */
	const teile = [
		{ value: 'kopf', label: 'Kopf (Titel, Bild, Verlauf)' },
		{ value: 'fakten', label: 'Fakten (Kunde, Branche …)' },
		{ value: 'kennzahlen', label: 'Kennzahlen' },
		{ value: 'leistungen', label: 'Leistungen' },
		{ value: 'galerie', label: 'Galerie' },
	];

	registerBlockType( 'konstrukta/projekt-ansicht', {
		apiVersion: 3,
		edit: function ( { attributes, setAttributes, context } ) {
			const blockProps = useBlockProps();
			const teil = teile.find( ( t ) => t.value === attributes.teil );
			// Im Template-Editor gibt es kein Projekt: ein Beispielprojekt zeigen.
			const previewId = context.postId && context.postType === 'kx_projekt' ? context.postId : KX.previewProjekt;
			return el( Fragment, null,
				el( InspectorControls, null, el( PanelBody, { title: 'Teil' },
					el( Field, { field: { key: 'teil', label: 'Welcher Teil?', type: 'select', options: teile }, value: attributes.teil, onChange: ( v ) => setAttributes( { teil: v } ) } ) ) ),
				el( 'div', blockProps,
					previewId
						? el( SSR, { block: 'konstrukta/projekt-ansicht', attributes, urlQueryArgs: { post_id: previewId } } )
						: el( Placeholder, { label: 'Projekt-Ansicht: ' + ( teil ? teil.label : attributes.teil ), instructions: 'Wird aus den Projektdaten des jeweiligen Referenzprojekts gefüllt.' } ) )
			);
		},
	} );

	const brancheField = KX.schemas.projekt.fields.find( ( f ) => f.key === 'branche' );
	const kartenFields = [
		{ key: 'anzahl', label: 'Anzahl', type: 'number', min: 1, max: 12 },
		{ key: 'nurHervorgehoben', label: 'Nur hervorgehobene Projekte', type: 'toggle' },
		{ key: 'branche', label: 'Branche', type: 'select', options: [ { value: '', label: 'Alle Branchen' } ].concat( brancheField.options ) },
	];
	registerBlockType( 'konstrukta/projekt-karten', {
		apiVersion: 3,
		edit: function ( { attributes, setAttributes } ) {
			const blockProps = useBlockProps();
			return el( Fragment, null,
				el( InspectorControls, null, el( PanelBody, { title: 'Auswahl' },
					kartenFields.map( ( f ) => el( 'div', { key: f.key, style: { marginBottom: 16 } },
						el( Field, { field: f, value: attributes[ f.key ], onChange: ( v ) => setAttributes( { [ f.key ]: v } ) } ) ) ) ) ),
				el( 'div', blockProps, el( SSR, { block: 'konstrukta/projekt-karten', attributes } ) )
			);
		},
	} );

	/* ------------------------------------------------------------------
	 * Block-Bindings-Quelle «Firmendaten»: lesen und schreiben.
	 * Schreibt in die Option kx_firma (Entität root/site). Speichern über
	 * den normalen Speichern-Dialog («Website-Einstellungen»).
	 * ---------------------------------------------------------------- */
	if ( registerBlockBindingsSource ) {
		const firma = ( select ) => {
			const canRead = select( 'core' ).canUser( 'read', { kind: 'root', name: 'site' } );
			const site = canRead ? select( 'core' ).getEditedEntityRecord( 'root', 'site' ) : null;
			return Object.assign( {}, KX.firma, ( site && site.kx_firma ) || {} );
		};
		registerBlockBindingsSource( {
			name: 'konstrukta/firma',
			label: 'Firmendaten',
			getValues( { select, bindings } ) {
				const data = firma( select );
				const out = {};
				Object.keys( bindings ).forEach( ( attr ) => {
					const key = bindings[ attr ].args && bindings[ attr ].args.key;
					out[ attr ] = data[ key ] !== undefined ? data[ key ] : '';
				} );
				return out;
			},
			setValues( { select, dispatch, bindings } ) {
				const data = firma( select );
				Object.keys( bindings ).forEach( ( attr ) => {
					const b = bindings[ attr ];
					if ( b.args && b.args.key ) {
						data[ b.args.key ] = b.newValue;
					}
				} );
				dispatch( 'core' ).editEntityRecord( 'root', 'site', undefined, { kx_firma: data } );
			},
			canUserEditValue( { select } ) {
				return !! select( 'core' ).canUser( 'update', { kind: 'root', name: 'site' } );
			},
			getFieldsList() {
				return Object.keys( KX.firmaFields ).map( ( key ) => ( { label: KX.firmaFields[ key ], type: 'string', args: { key } } ) );
			},
		} );
	}

	/* ------------------------------------------------------------------
	 * Bindings-Quelle «Seitenfelder» (nur lesend): Rubrik als Bezeichnung.
	 * Liest live aus dem Editor – ändert man die Rubrik im DataForm,
	 * ändert sich die Zeile im Seitenkopf sofort.
	 * ---------------------------------------------------------------- */
	if ( registerBlockBindingsSource ) {
		const seite = KX.schemas.seite;
		registerBlockBindingsSource( {
			name: 'konstrukta/seite',
			label: 'Seitenfelder',
			usesContext: [ 'postId', 'postType' ],
			getValues( { select, bindings } ) {
				const meta = select( 'core/editor' ).getEditedPostAttribute( 'meta' ) || {};
				const out = {};
				Object.keys( bindings ).forEach( ( attr ) => {
					const key = bindings[ attr ].args && bindings[ attr ].args.key;
					const field = seite.fields.find( ( f ) => f.key === key );
					let v = meta[ seite.prefix + key ];
					if ( field && field.options ) {
						const o = field.options.find( ( x ) => x.value === ( v || field.default ) );
						v = o ? o.label : v;
					}
					out[ attr ] = v || '';
				} );
				return out;
			},
			canUserEditValue() {
				return false;
			},
		} );
	}

	registerBlockType( 'konstrukta/footer-links', {
		apiVersion: 3,
		edit: function () {
			return el( 'div', useBlockProps(), el( SSR, { block: 'konstrukta/footer-links' } ) );
		},
	} );

	/* ------------------------------------------------------------------
	 * Workaround: Wer aus einem Referenzprojekt heraus «Template bearbeiten»
	 * wählt, behält im Template-Modus die Einstellungen template/templateLock
	 * des Inhaltstyps (Core übernimmt settings.template unverändert). Folge:
	 * Hinweis «Inhalt passt nicht zum Template», das Template ist gesperrt,
	 * und «Template zurücksetzen» würde die Projektdaten-Maske ins
	 * Site-Template einfügen. Deshalb im Template-Modus beides entfernen und
	 * beim Zurückkehren wiederherstellen.
	 * ---------------------------------------------------------------- */
	if ( wp.data.select( 'core/editor' ) ) {
		let lastType = null;
		let stash = null;
		wp.data.subscribe( () => {
			const ed = wp.data.select( 'core/editor' );
			const type = ed.getCurrentPostType && ed.getCurrentPostType();
			// Die Gültigkeit wird nach dem Laden der Template-Blöcke erneut gesetzt.
			if ( stash && type === 'wp_template' && ! wp.data.select( 'core/block-editor' ).isValidTemplate() ) {
				wp.data.dispatch( 'core/block-editor' ).setTemplateValidity( true );
			}
			if ( ! type || type === lastType ) {
				return;
			}
			lastType = type;
			const settings = ed.getEditorSettings();
			const dispatch = wp.data.dispatch( 'core/editor' );
			if ( type === 'wp_template' && settings.template ) {
				stash = { template: settings.template, templateLock: settings.templateLock };
				dispatch.updateEditorSettings( { template: undefined, templateLock: false } );
				wp.data.dispatch( 'core/block-editor' ).setTemplateValidity( true );
			} else if ( type !== 'wp_template' && stash ) {
				dispatch.updateEditorSettings( stash );
				stash = null;
			}
		} );
	}

	// Für das DataForm-Bundle und Tests.
	window.kxSchemaForm = { Field, RowEditor, MediaCell, GradientCell, SchemaForm, clean, isEmpty };
} )( window.wp );
