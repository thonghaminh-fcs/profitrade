<?php

/**
 * Search Bar block — server-side render.
 *
 * @var array    $attributes Block attributes
 * @var string   $content    Inner content
 * @var WP_Block $block      Block instance
 */

defined('ABSPATH') || exit;

$display_mode  = $attributes['displayMode']  ?? 'inline';
$search_scope  = $attributes['searchScope']  ?? 'products';
$placeholder   = esc_attr($attributes['placeholder'] ?? __('Search products…', 'ai-zippy'));
$max_results   = (int) ($attributes['maxResults'] ?? 8);
$icon_url      = esc_url($attributes['iconUrl'] ?? '');
$icon_size     = max(12, min(48, (int) ($attributes['iconSize'] ?? 20)));
$uid           = wp_unique_id('zs-');

// Native GET-search fallbacks so the block still works when the typeahead JS is
// disabled (feature flag off) or fails to load. initSearchBar() enhances these:
// it intercepts the inline form submit and the icon trigger to run the dropdown.
$fallback_action = esc_url(home_url('/'));
$scope_hidden    = ($search_scope === 'products')
    ? '<input type="hidden" name="post_type" value="product">'
    : '';
$fallback_browse = home_url('/');
if ($search_scope === 'products' && function_exists('wc_get_page_permalink')) {
    $shop = wc_get_page_permalink('shop');
    if ($shop) {
        $fallback_browse = $shop;
    }
}
$fallback_browse = esc_url($fallback_browse);

$wrapper_attrs = get_block_wrapper_attributes([
    'class'            => 'zs__block zs__block--' . esc_attr($display_mode),
    'data-scope'       => esc_attr($search_scope),
    'data-max-results' => $max_results,
    'data-mode'        => esc_attr($display_mode),
    'style'            => '--zs-icon-size:' . $icon_size . 'px',
]);

$svg_search  = '<svg width="' . $icon_size . '" height="' . $icon_size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>';
$svg_close   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>';
$icon_markup = $icon_url
    ? '<img src="' . $icon_url . '" alt="" width="' . $icon_size . '" height="' . $icon_size . '" class="zs__custom-icon" aria-hidden="true">'
    : $svg_search;
?>

<div <?php echo $wrapper_attrs; ?>>

	<?php if ($display_mode === 'icon') : ?>

		<!-- Icon-only trigger. A real link (to browse/search) so it still goes
		     somewhere useful when the typeahead JS is off; JS turns it into the
		     modal opener. -->
		<a
			class="zs__icon-trigger"
			href="<?php echo $fallback_browse; ?>"
			role="button"
			aria-label="<?php esc_attr_e('Open search', 'ai-zippy'); ?>"
			aria-expanded="false"
			aria-controls="<?php echo esc_attr($uid); ?>-modal"
		>
			<?php echo $icon_markup; // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>

		<!-- Full-page modal (shown on trigger click) -->
		<div class="zs__modal" id="<?php echo esc_attr($uid); ?>-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Search', 'ai-zippy'); ?>" hidden>
			<div class="zs__modal-backdrop"></div>
			<div class="zs__modal-inner">
				<div class="zs__modal-header">
					<span class="zs__modal-title"><?php esc_html_e('Search', 'ai-zippy'); ?></span>
					<button class="zs__modal-close" type="button" aria-label="<?php esc_attr_e('Close search', 'ai-zippy'); ?>">
						<?php echo $svg_close; // phpcs:ignore ?>
						<span><?php esc_html_e('Close', 'ai-zippy'); ?></span>
					</button>
				</div>
				<div class="zs__input-wrap">
					<?php echo $svg_search; // phpcs:ignore ?>
					<input
						class="zs__input"
						type="search"
						placeholder="<?php echo $placeholder; ?>"
						autocomplete="off"
						spellcheck="false"
						aria-label="<?php esc_attr_e('Search', 'ai-zippy'); ?>"
						aria-autocomplete="list"
						aria-controls="<?php echo esc_attr($uid); ?>-results"
					/>
					<button class="zs__clear" type="button" aria-label="<?php esc_attr_e('Clear search', 'ai-zippy'); ?>" hidden>
						<?php echo $svg_close; // phpcs:ignore ?>
					</button>
				</div>
				<div class="zs__results" id="<?php echo esc_attr($uid); ?>-results" role="listbox" aria-label="<?php esc_attr_e('Search results', 'ai-zippy'); ?>"></div>
				<div class="zs__footer">
					<span class="zs__hint">
						<kbd>↑↓</kbd> <?php echo esc_html_x('navigate', 'keyboard hint verb', 'ai-zippy'); ?> &nbsp;
						<kbd>Enter</kbd> <?php echo esc_html_x('select', 'keyboard hint verb', 'ai-zippy'); ?> &nbsp;
						<kbd>Esc</kbd> <?php echo esc_html_x('close', 'keyboard hint verb', 'ai-zippy'); ?>
					</span>
				</div>
			</div>
		</div>

	<?php else : ?>

		<!-- Inline search bar. A real GET form so typing + Enter reaches native
		     WordPress search when the typeahead JS is off (feature flag) or fails
		     to load; initSearchBar() intercepts submit to keep the live dropdown. -->
		<form class="zs__form" role="search" method="get" action="<?php echo $fallback_action; ?>">
			<div class="zs__input-wrap">
				<?php echo $icon_markup; // phpcs:ignore ?>
				<input
					class="zs__input"
					type="search"
					name="s"
					placeholder="<?php echo $placeholder; ?>"
					autocomplete="off"
					spellcheck="false"
					aria-label="<?php esc_attr_e('Search', 'ai-zippy'); ?>"
					aria-autocomplete="list"
					aria-controls="<?php echo esc_attr($uid); ?>-results"
				/>
				<button class="zs__clear" type="button" aria-label="<?php esc_attr_e('Clear search', 'ai-zippy'); ?>" hidden>
					<?php echo $svg_close; // phpcs:ignore ?>
				</button>
			</div>
			<?php echo $scope_hidden; // phpcs:ignore ?>
		</form>
		<div class="zs__results zs__results--inline" id="<?php echo esc_attr($uid); ?>-results" role="listbox" aria-label="<?php esc_attr_e('Search results', 'ai-zippy'); ?>"></div>

	<?php endif; ?>

</div>
