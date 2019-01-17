/**
	@brief		Checkboxize and collapse groups in the table.
	@details

	$( 'table.plugin_pack.with_groups' ).plainview_table_with_groups( {
		'collapsible' : 0,		// How many group items before checkboxing
		'collapse' : 1000,		// How many items before starting collapsed.
	} );

	@since		2015-12-01 17:11:43
**/
;(function( $ )
{
    $.fn.extend(
    {
        plainview_table_with_groups : function( options )
        {
        	options = $.extend( {
        		'collapsible' : 5,		// How many rows to have before the collapse control appears.
        		'collapse' : 10,		// How many rows to have before automatically collapsing on init.
        	}, options );

            return this.each( function()
            {
                var $$ = $(this);

                // Check that we haven't already grouped this table.
                if ( $$.data( 'plainview_table_with_groups' ) == true )
                	return;
                $$.data( 'plainview_table_with_groups', true );

                /**
                	@brief		Add a collapse / uncollapse control for large groups.
                	@since		2015-12-01 16:09:33
                **/
                $$.add_collapsers = function()
                {
					$.each( $( 'tr.group', $$ ), function( index, row )
					{
						var $row = $( row );

						var group = $row.data( 'group' );

						// How many rows have this group?
						var $rows = $( 'tr.plugin[data-group="' + group + '"]' );

						// We need a minimum count.
						if ( $rows.length < options.collapsible )
							return;

						var $th_name = $( 'th.name', $row );

						// Create a collapse / uncollapse
						var $control = $( '<span></span>' )
							.addClass( 'collapser' )
							.data( 'collapsed', false )
							.prependTo( $th_name );

						/**
							@brief		Set the correct arrow when un/collapsing.
							@since		2015-12-04 23:16:16
						**/
						$control.set_html = function()
						{
							var $this = $( this );
							var collapsed = $this.data( 'collapsed' );
							if ( collapsed )
								html = '<span class="dashicons dashicons-arrow-down-alt2"></span>';
							else
								html = '<span class="dashicons dashicons-arrow-up-alt2"></span>';

							$this.html( html + '&emsp;' );
						}

						/**
							@brief		Set the correct un/collapsed class for the row.
							@since		2015-12-04 23:16:34
						**/
						$row.set_collapsed_class = function( collapsed )
						{
							if ( collapsed )
								$row.addClass( 'collapsed' ).removeClass( 'uncollapsed' );
							else
								$row.removeClass( 'collapsed' ).addClass( 'uncollapsed' );
						}

						/**
							@brief		Un/collapse when clicking.
							@since		2015-12-04 23:16:48
						**/
						$th_name.click( function()
							{
								var collapsed = $control.data( 'collapsed' );
								collapsed = ! collapsed;

								$control.data( 'collapsed', collapsed );
								$row.set_collapsed_class( collapsed );

								var $check_column = $( 'th.check-column input', $row );
								if ( ! collapsed )
									$check_column.fadeTo( 'fast', 1.0 );
								else
									$check_column.fadeTo( 'fast', 0 );

								$rows.toggle( 'fast', function()
								{
									$control.set_html();
								});
							} )
							.css( 'cursor', 'pointer' );

						if ( $rows.length >= options.collapse )
							$th_name.click();
						else
						{
							$control.set_html();
							$row.set_collapsed_class( false );
						}

						// It's a good idea to have an active / inactive counter.
						var $active = $( 'tr.plugin.active[data-group="' + group + '"]' );
						$( 'th.name', $row ).append( ' <small>' + $active.length + ' / ' + $rows.length + '</small>');
					} );
				}

                /**
                	@brief		Add a group checkbox.
                	@since		2015-12-01 16:08:35
                **/
                $$.add_group_cb = function()
                {
					// Find all group rows.
					$.each( $( 'tr.group', $$ ), function( index, row )
					{
						var $row = $( row );

						// Make the colspan one less, due to the upcoming cb.
						var $th = $( 'th', $row );
						var colspan = $th.prop( 'colspan' );
						colspan = colspan - 1;
						$th.prop( 'colspan', colspan );
						$th.addClass( 'name' );

						// Insert a checkbox.
						var $th_cb = $( '<th class="check-column"></th>' );
						var $cb = $( '<input type="checkbox" scope="row">' ).click( function()
						{
							var $this = $( this );

							// What is our state?
							var checked = $this.prop( 'checked' );

							// Find our group slug.
							var group_slug = $this.parent().parent().data( 'group' );

							// Check / uncheck "our" checkboxes
							var selector = 'tr.plugin[data-group="' + group_slug + '"] th.check-column input';
							$( selector, $$ ).prop( 'checked', checked );
						} );
						$cb.appendTo( $th_cb );
						$th_cb.prependTo( $row );
					} );
                }

				/**
					@brief		Add un/collapsers for the entire table.
					@since		2015-12-04 23:18:30
				**/
				$$.add_table_collapsers = function()
				{
					var $table_collapsers = $( '<p></p>' )
						.css( 'cursor', 'pointer' )
						.appendTo( $$.parent() );

					var $table_uncollapser = $( '<span><small><span class="dashicons dashicons-arrow-down-alt2"></span> Expand all</small></span>' )
						.click( function()
						{
							$( '.collapser', $$ ).map( function()
							{
								var $this = $( this );
								if ( $this.data( 'collapsed' ) == true )
									$this.click();
							} );
						} )
						.appendTo( $table_collapsers );

					$table_collapsers.append( '&emsp;&emsp;' );

					var $table_collapser = $( '<span><small><span class="dashicons dashicons-arrow-up-alt2"></span> Collapse all</small></span>' )
						.click( function()
						{
							$( '.collapser', $$ ).map( function()
							{
								var $this = $( this );
								if ( $this.data( 'collapsed' ) == false )
									$this.click();
							} );
						} )
						.appendTo( $table_collapsers );
					// end: Add expanders and collapsers for the entire table.
                }

                /**
                	@brief		Steal the color from the adminmenu and use it as a group background.
                	@since		2015-12-04 22:56:32
                **/
                $$.steal_colors = function()
                {
                	// Extract the colors of the active menu item.
                	var color = $( '#adminmenu a.wp-menu-open' ).css( 'color' );
                	var background_color = $( '#adminmenu a.wp-menu-open' ).css( 'background-color' );

                	// Assemble the style text.
                	var text = 'table.with_groups tr.group.uncollapsed { background-color : ' + background_color + '; }';
                	text += 'table.with_groups tr.group.uncollapsed th { color: ' + color + '; }';

                	// Create the style element and put it before the table.
                	var $style = $( '<style>' )
                		.text( text )
                		.prependTo( $$.parent() );
                }

                // The group cb adds the name class, which is required for the collapsers.
                $$.add_group_cb();
                $$.add_collapsers();
                $$.add_table_collapsers();
                $$.steal_colors();

            } ); // return this.each( function()
        } // plugin: function()
    } ); // $.fn.extend({
} )( jQuery );
