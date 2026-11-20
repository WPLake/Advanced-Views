<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups;

defined( 'ABSPATH' ) || exit;

use Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\CreatorInterface;

/**
 * The optional (template) part of a Post Selection: a templateless Selection is query-only.
 */
abstract class Templated_Selection_Settings extends Cpt_Settings {
	const PAGINATION_TYPE_LOAD_MORE_BUTTON = 'load_more_button';
	const PAGINATION_TYPE_INFINITY         = 'infinity_scroll';
	const PAGINATION_TYPE_PAGE_NUMBERS     = 'page_numbers';
	const MAGIC_CSS_SELECTOR               = 'selection';

	/**
	 * @a-type tab
	 * @label Mount Points
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 450
	 */
	public bool $mount_points_tab;
	/**
	 * @item \Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings
	 * @var Mount_Point_Settings[]
	 * @label Mount Points
	 * @instructions 'Mount' this item to a location that doesn't support shortcodes. Mounting uses 'the_content' theme hook. <a target="_blank" href="https://docs.advanced-views.com/display-content/mount-points-pro">Read more</a>
	 * @button_label Add Mount Point
	 * @a-no-tab 1
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 460
	 */
	public array $mount_points;
	/**
	 * Deprecated: a templateless (false) Selection is query-only, the flag is set only by the migration.
	 *
	 * @a-deprecated This argument is used just to hide the field from the UI
	 */
	public bool $has_template;
	/**
	 * @a-type av_slug_select
	 * @allow_null 1
	 * @label Layout for Results
	 * @instructions Assigned Layout is used to display every post from the query results.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 200
	 */
	public string $acf_view_id;
	/**
	 * @label No Posts Found Message
	 * @instructions Add a message that will be displayed if there are no results. Leave empty for no message.
	 * @default_value No posts found
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 210
	 */
	public string $no_posts_found_message;
	/**
	 * @a-type select
	 * @label Enable Slider
	 * @instructions Select the slider library to enable. <br> Customize the slider after saving, by editing the JS Code in the Styles & Scripts tab.
	 * @choices {"none":"None","splide_v4":"Splide v4 (29.8KB js, 5KB css)"}
	 * @default_value none
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 220
	 */
	public string $slider_type;
	/**
	 * @label Enable Responsive rules
	 * @instructions When enabled responsive CSS styles are added to the CSS Code field. These styles are automatically updated each time you save. <br>Tip: If you’d like to edit the Post Selection CSS manually, simply disable this option. Disabling this does not remove the previously added CSS Code.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 240
	 */
	public bool $is_use_layout_css;
	/**
	 * @var Post_Selection_Layout_Settings[]
	 * @item \Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups\Post_Selection_Layout_Settings
	 * @label Responsive Rules
	 * @instructions These rules control Post Selection items responsiveness. <br>Note: These rules are inherited from small to large. For example: If you’ve set up 'Mobile' and 'Desktop' screen rules, then 'Tablet' will have the same rules as 'Mobile' and 'Large Desktop' will have the same rules as 'Desktop'.
	 * @button_label Add Rule
	 * @a-no-tab 1
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 250
	 */
	public array $layout_rules;
	/**
	 * @a-type tab
	 * @label Pagination
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "!=","value": "context_posts"},{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 260
	 */
	public bool $pagination_tab;
	/**
	 * @label With Pagination
	 * @instructions If enabled then the selected pagination type is applied and the 'Posts per page' rule takes effect. <a target='_blank' href='https://docs.advanced-views.com/post-selections/features/posts-pagination'>Read more</a>.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "!=","value": "context_posts"},{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 270
	 */
	public bool $is_with_pagination;
	/**
	 * @a-type select
	 * @label Pagination Type
	 * @instructions Defines a way in which user can load more. For 'Load More Button' and 'Page Numbers' cases a special markup will be added to the Post Selection automatically, you can style it in the Styles & Scripts tab.
	 * @choices {"load_more_button":"Load More Button","infinity_scroll":"Infinity Scroll","page_numbers":"Page Numbers"}
	 * @default_value load_more_button
	 * @a-pro-choices ["load_more_button","infinity_scroll"]
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "!=","value": "context_posts"},{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 280
	 */
	public string $pagination_type;
	/**
	 * @label 'Load More' button label
	 * @instructions Define a Custom label for the load more button.
	 * @default_value Load more
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__pagination-type","operator": "==","value": "load_more_button"},{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "!=","value": "context_posts"},{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 290
	 */
	public string $load_more_button_label;
	/**
	 * @label Posts Per Page
	 * @instructions Controls how many posts will be displayed initially and how many posts will be appended every time when user triggers 'Load More'. Total amount of posts is limited by the 'Maximum number of posts' field in the 'Query' tab.
	 * @default_value 9
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "!=","value": "context_posts"},{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 300
	 */
	public int $pagination_per_page;
	/**
	 * @a-type tab
	 * @label Template
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 310
	 */
	public bool $template_tab;
	/**
	 * @a-type textarea
	 * @new_lines br
	 * @label Default Selection Template
	 * @instructions Output preview of the generated template using the <a target='_blank' href='https://docs.advanced-views.com/features/smart-templates'>chosen template engine</a>. <br> Important! Publish or Update your Post Selection to see the latest markup.
	 * @disabled 1
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 320
	 */
	public string $markup;
	/**
	 * @a-type textarea
	 * @label Custom Selection Template
	 * @instructions Write your own template with full control over the HTML markup. <br> Copy the Default Template code and make your changes. <br><br> Check out our Docs to learn more about the <a target='_blank' href='https://docs.advanced-views.com/features/smart-templates'>available template engines</a>. <br><br> Press Ctrl (Cmd) + Alt + L to format the code. Press Ctrl + F to search (or replace). <br><br> Make sure you've retained all the default classes; otherwise, pagination won't work.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 330
	 */
	public string $custom_markup;
	/**
	 * @a-type select
	 * @label Template Engine
	 * @instructions Choose one of the <a target='_blank' href='https://docs.advanced-views.com/features/smart-templates'>supported template engines</a>, which will be used for this Post Selection.
	 * @default_value twig
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 340
	 */
	public string $template_engine;
	/**
	 * @a-type tab
	 * @label Styles & Scripts
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 350
	 */
	public bool $css_and_js_tab;
	/**
	 * @a-type textarea
	 * @label CSS Code
	 * @instructions Define your CSS style rules. <br> Rules defined here will be added within &lt;style&gt;&lt;/style&gt; tags ONLY to pages that have this Post Selection. <br><br> Press Ctrl (Cmd) + Alt + L to format the code; Ctrl + F to search/replace; Ctrl + Space for autocomplete. <br><br> Magic shortcuts are available: <br><br>  1. '#selection' as a unique instance selector, will be replaced with '.avf-selection--id--{x}' <br> 2. '#selection__' as a full element selector, so '#selection__element' will be replaced with '.avf-selection--id--{x} .avf-selection__element' <br> 3. '#this' as a short element selector, so '#this__element' will be replaced with '.avf-selection__element' <br> Note: all the shortcuts are compatible with the BEM name option.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 360
	 */
	public string $css_code;
	/**
	 * @a-type textarea
	 * @label JavaScript Code
	 * @instructions Configure libraries or add custom code to your Post Selection.<br><br> By default, the Post Selection is a <a target='_blank' href='https://docs.advanced-views.com/post-selections/code-fields/javascript-code'>web component</a>, so this code will be executed once for every instance, and 'this', that refers to the current instance, is available. <br><br> If the Web Component Type is set to none, the js code here is plain, and can be used for any goals, including <a target='_blank' href='https://docs.advanced-views.com/post-selections/component-options/interactivity-api'>WP Interactivity API</a>. <br><br> The code snippet will be added within &lt;script type='module'&gt;&lt;/script&gt; tags ONLY to pages that have this Post Selection. <br><br> Press Ctrl (Cmd) + Alt + L to format the code. Press Ctrl + F to search (or replace).
	 * @a-pro The field must be not required or have default value!
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 370
	 */
	public string $js_code;
	/**
	 * @a-type select
	 * @label Web Component Type
	 * @instructions By default, every Post Selection is a <a target='_blank' href='https://docs.advanced-views.com/post-selections/code-fields/javascript-code'>web component</a>, which allows you to work easily with the element in the JS code field. <br><br> Set it to 'None' if you're going to use the <a target='_blank' href='https://docs.advanced-views.com/post-selections/component-options/interactivity-api'>WP Interactivity API</a>.
	 * @choices {"classic":"Classic (no CSS isolation)","shadow_root_template":"Declarative Shadow DOM (CSS isolated, server-side)","shadow_dom":"JS Shadow DOM (CSS isolated, client-side)","none":"None"}
	 * @default_value classic
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 400
	 */
	public string $web_component;
	/**
	 * @a-type select
	 * @label Classes generation
	 * @instructions Controls classes generation in the Default Template.
	 * @choices {"bem":"BEM style","none":"None"}
	 * @default_value bem
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 410
	 */
	public string $classes_generation;
	/**
	 * @label BEM Unique Name
	 * @instructions Define a unique <a target='_blank' href='https://getbem.com/introduction/'>BEM name</a> for the element that will be used in the markup, or leave it empty to use the default ('avf-selection').
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 420
	 */
	public string $bem_name;
	/**
	 * @label CSS classes
	 * @instructions Add a class name without a dot (e.g. 'class-name') or multiple classes with single space as a delimiter (e.g. 'class-name1 class-name2'). These classes are added to the wrapping HTML element. <a target='_blank' href='https://www.w3schools.com/cssref/sel_class.asp'>Learn more about CSS Classes</a>.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 430
	 */
	public string $css_classes;
	/**
	 * @a-type true_false
	 * @label Use the Post ID as the Post Selection ID in the markup
	 * @instructions Note: For backward compatibility purposes only. Enable this option if you have external CSS selectors that rely on outdated digital IDs.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__has-template","operator": "==","value": "1"}]]
	 * @a-order 440
	 */
	public bool $is_markup_with_digital_id;

	// cache.
	private string $no_posts_found_message_translation;
	private string $load_more_button_label_translation;

	public function __construct( CreatorInterface $creator ) {
		parent::__construct( $creator );

		$this->no_posts_found_message_translation = '';
		$this->load_more_button_label_translation = '';
	}

	public function has_unique_bem_name(): bool {
		$bem_name  = trim( $this->bem_name );
		$is_filled = strlen( $bem_name ) > 0;

		$cpt_name      = Hard_Post_Selection_Cpt::cpt_name();
		$default_names = array( 'acf-card', $cpt_name );
		$is_default    = in_array( $bem_name, $default_names, true );

		return $is_filled && ! $is_default;
	}

	public function get_css_code( string $mode ): string {
		$aliases  = array( 'card', self::MAGIC_CSS_SELECTOR );
		$css_code = $this->css_code;

		foreach ( $aliases as $alias ) {
			$css_code = $this->resolved_css_code( $css_code, $mode, $alias );
		}

		// back the right way, as before it was hack for CodeMirror.
		$css_code = str_replace( '"1fr"', '1fr', $css_code );

		return trim( $css_code );
	}

	/**
	 * @return array<string,string[]>
	 */
	public function get_multilingual_strings(): array {
		return $this->get_multilingual_strings_from_labels();
	}

	public function has_template(): bool {
		return $this->has_template;
	}

	public function is_with_pagination(): bool {
		return $this->has_template &&
				$this->is_with_pagination;
	}

	public function get_pagination_per_page(): int {
		return $this->pagination_per_page;
	}

	public function get_bem_name(): string {
		$bem_name  = trim( $this->bem_name );
		$is_filled = strlen( $bem_name ) > 0;

		$default_name = Hard_Post_Selection_Cpt::markup_name();

		if ( $is_filled ) {
			$sanitized = self::sanitize_bem_name( $bem_name );

			return is_string( $sanitized ) ?
				$sanitized :
				$default_name;
		}

		return $default_name;
	}

	public function get_no_posts_found_message_translation(): string {
		$is_filled     = strlen( $this->no_posts_found_message ) > 0;
		$is_translated = strlen( $this->no_posts_found_message_translation ) > 0;

		if ( $is_filled && ! $is_translated ) {
			$this->no_posts_found_message_translation = Plugin::get_label_translation( $this->no_posts_found_message );
		}

		return $this->no_posts_found_message_translation;
	}

	public function get_load_more_button_label_translation(): string {
		$is_filled     = strlen( $this->load_more_button_label ) > 0;
		$is_translated = strlen( $this->load_more_button_label_translation ) > 0;

		if ( $is_filled && ! $is_translated ) {
			$this->load_more_button_label_translation = Plugin::get_label_translation( $this->load_more_button_label );
		}

		return $this->load_more_button_label_translation;
	}

	/**
	 * @return string[]
	 */
	protected function get_used_meta_group_ids(): array {
		return $this->has_template ?
			array( $this->acf_view_id ) :
			array();
	}

	/**
	 * @return array<string,string[]>
	 */
	protected function get_multilingual_strings_from_labels(): array {
		$labels             = array();
		$is_with_not_found  = strlen( $this->no_posts_found_message ) > 0;
		$is_with_load_more  = strlen( $this->load_more_button_label ) > 0;

		if ( $is_with_not_found ) {
			$labels[] = $this->no_posts_found_message;
		}

		if ( $is_with_load_more ) {
			$labels[] = $this->load_more_button_label;
		}

		$is_with_labels = count( $labels ) > 0;
		$text_domain    = Plugin::get_theme_text_domain();

		return $is_with_labels ?
			array(
				$text_domain => $labels,
			) :
			array();
	}
}
