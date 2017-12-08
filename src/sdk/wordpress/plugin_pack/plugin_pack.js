<script type="text/javascript">
	jQuery( document ).ready( function( $ )
	{
		var $table = $( 'table.plugin_pack.plugins.with_groups' );

		if ( $table.len < 1 )
			return;

		// Collect all of the available groups.
		var active_plugin_count = [];
		var group_names = {};
		var inactive_plugin_count = [];

		var active_count = 0;
		var inactive_count = 0;
		var total_count = 0;

		var last_group = '';

		// All should come first.
		group_names[ 'all' ] = 'All';

		// Collect all group info.
		$.each( $( 'tr.group', $table ), function( index, item )
		{
			var $item = $( item );
			var group_slug = $item.data( 'group' );
			var group_name = $( 'th', $item ).text();

			active_plugin_count[ group_slug ] = $( 'tr.active.plugin[data-group=' + group_slug + ']', $table ).length;
			active_count += active_plugin_count[ group_slug ];
			group_names[ group_slug ] = group_name;
			inactive_plugin_count[ group_slug ] = $( 'tr.inactive.plugin[data-group=' + group_slug + ']', $table ).length;
			inactive_count += inactive_plugin_count[ group_slug ];

			total_count += active_plugin_count[ group_slug ];
			total_count += inactive_plugin_count[ group_slug ];

			last_group = group_slug;
		} );

		// Only display the index if there is more than one pack.
		if ( Object.keys( group_names ).length < 3 )
			return;

		// Add the index.
		var $plugin_index = $( '<ul>' )
			.addClass( 'subsubsub' );

		var $table_container = $table.parentsUntil( 'div.tab_contents' ).parent();

		$table_container.prepend( '<div class="clear" />' );

		$plugin_index.prependTo( $table_container );

		// Add the ALL group
		var slug = 'all';
		active_plugin_count[ slug ] = active_count;
		inactive_plugin_count[ slug ] = inactive_count;

		// All the group tabs.
		$.each( group_names, function( group_slug, group_name )
		{
			var total_plugin_count = active_plugin_count[ group_slug ] + inactive_plugin_count[ group_slug ];
			var counts = ' <small><span title="Active plugins in this group">(' + active_plugin_count[ group_slug ] + '</span> / <span title="Total plugins in this group">' + total_plugin_count + ' )</span></small>';
			var href = '<a href="#">' + group_name + '</a>' + counts;

			var li_html = href;

			if ( group_slug != last_group )
				href = href + '<span class="sep">&nbsp;&#124;&nbsp;</span>';

			var $li = $( '<li>' )
				.html( href );

			$li.click( function()
			{
				if ( group_slug != 'all' )
				{
					// Hide everything.
					$( 'tr', $table ).hide();
					// Show only this group.
					$( 'tr[data-group="' + group_slug + '"]', $table ).show();
				}
				else
				{
					// Show everything.
					$( 'tr', $table ).show();
				}

				// Remove current from all lis.
				$( 'li a', $plugin_index ).removeClass( 'current' );

				// Add current to just this li.
				$( 'a', this ).addClass( 'current' );
			} );

			$plugin_index.append( $li );
		} );

		// Click the first (All) in order to mark everything properly.
		$( 'li a', $plugin_index ).first().click();
	} );
</script>