<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups;

defined( 'ABSPATH' ) || exit;

use Exception;
use Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings;
use Org\Wplake\Advanced_Views\Acf\Groups\Parents\Cpt_Settings;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Layout_Cpt;
use Org\Wplake\Advanced_Views\Plugin\Plugin;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\FieldInfoInterface;

class Layout_Settings extends Cpt_Settings {
	// to fix the group name in case class name changes.
	const CUSTOM_GROUP_NAME           = self::GROUP_NAME_PREFIX . 'view';
	const LOCATION_RULES              = array(
		array(
			'post_type == ' . Hard_Layout_Cpt::NAME,
		),
	);
	const FIELD_GROUP                 = 'group';
	const FIELD_PARENT_FIELD          = 'parent_field';
	const FIELD_MARKUP                = 'markup';
	const FIELD_CSS_CODE              = 'css_code';
	const FIELD_JS_CODE               = 'js_code';
	const FIELD_CUSTOM_MARKUP         = 'custom_markup';
	const FIELD_PHP_VARIABLES         = 'php_variables';
	const FIELD_TEMPLATE_TAB          = 'template_tab';
	const FIELD_CSS_AND_JS_TAB        = 'css_and_js_tab';
	const FIELD_DEVELOPER_TAB         = 'developer_tab';
	const FIELD_SOURCE_MENUS          = 'source_menus';
	const FIELD_SOURCE_ROLES_WITH     = 'source_roles_with';
	const FIELD_SOURCE_ROLES_WITHOUT  = 'source_roles_without';
	const ITEM_TYPE_POST              = 'post';
	const ITEM_TYPE_USER              = 'user';
	const ITEM_TYPE_TERM              = 'term';
	const ITEM_TYPE_COMMENT           = 'comment';
	const ITEM_TYPE_MENU              = 'menu';
	const ITEM_TYPE_CUSTOM            = 'custom';
	const ITEM_FORMAT_SINGLE          = 'single';
	const ITEM_FORMAT_MULTIPLE        = 'multiple';
	const SOURCE_TYPE_CONTEXT         = 'context';
	const SOURCE_TYPE_DEFINED         = 'defined';
	const POST_FIELD_IS_HAS_GUTENBERG = 'post_mime_type';
	// keep the WP format 'image/jpg' to use WP_Query without issues.
	const POST_VALUE_IS_HAS_GUTENBERG = 'block/block';
	const UNIQUE_ID_PREFIX            = 'view_';
	const MAGIC_CSS_SELECTOR          = 'layout';


	/**
	 * @label Layout Name
	 * @required 1
	 * @instructions The internal name of the Layout, visible on the list screen.
	 * @a-html-class advanced-views__title
	 */
	public string $title;
	/**
	 * @a-type tab
	 * @label Mount Points
	 * @a-order 2
	 */
	public bool $mount_points_tab;
	/**
	 * @item \Org\Wplake\Advanced_Views\Acf\Groups\Mount_Point_Settings
	 * @var Mount_Point_Settings[]
	 * @label Mount Points
	 * @instructions 'Mount' this item to a location that doesn't support shortcodes. Mounting uses 'the_content' theme hook. <a target="_blank" href="https://docs.advanced-views.com/display-content/mount-points-pro">Read more</a>
	 * @button_label Add Mount Point
	 * @a-no-tab 1
	 * @a-order 2
	 */
	public array $mount_points;

	/**
	 * @a-type tab
	 * @label Item Settings
	 */
	public bool $item_settings_tab;

	/**
	 * @a-type select
	 * @label Item type
	 * @instructions The type of the data object the Layout displays.
	 * @choices {"post":"Post (Page, CPT, Product)","user":"User","term":"Taxonomy term","comment":"Comment","menu":"Menu","custom":"Custom (e.g. Options page)"}
	 * @default_value post
	 */
	public string $item_type;
	/**
	 * @a-type select
	 * @label Item format
	 * @instructions 'Single item' Layout displays one object, 'Multiple items' displays a set of the objects.
	 * @choices {"single":"Single item","multiple":"Multiple items (loop)"}
	 * @default_value single
	 * @conditional_logic [[{"field": "local_acf_views_view__item-type","operator": "!=","value": "custom"}]]
	 */
	public string $item_format;

	/**
	 * @item \Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Item_Settings
	 * @var Item_Settings[]
	 * @label Item Fields
	 * @instructions Assign item fields to your Layout. <br> Tip: hover mouse on the field number column and drag to reorder.
	 * @button_label Add Field
	 * @collapsed local_acf_views_field__key
	 * @a-no-tab 1
	 */
	public array $items;

	/**
	 * @a-type select
	 * @return_format value
	 * @allow_null 1
	 * @ui 1
	 * @label Parent group (for Nested repeater or Flexible layout)
	 * @instructions Choose a Parent group when setting up Nested repeater or Flexible layout.
	 */
	public string $group;

	/**
	 * @a-type select
	 * @return_format value
	 * @allow_null 1
	 * @label Parent field
	 * @instructions If you're making an internal Layout for the <a target='_blank' href='https://docs.advanced-views.com/field-types/layout/group-field'>group</a>, <a target='_blank' href='https://docs.advanced-views.com/field-types/layout/repeater-field'>repeater</a> or <a target='_blank' href='https://docs.advanced-views.com/field-types/layout/flexible-field'>flexible</a> field, then fill out this field.
	 * @a-pro The field must be not required or have default value!
	 * @conditional_logic [[{"field": "local_acf_views_view__group","operator": "!=","value": ""}]]
	 */
	public string $parent_field;

	/**
	 * @a-type tab
	 * @label Template
	 */
	public bool $template_tab;
	/**
	 * @a-type textarea
	 * @new_lines br
	 * @label Default Template
	 * @instructions Output preview of the generated template, using the <a target='_blank' href='https://docs.advanced-views.com/features/smart-templates'>chosen template engine</a>. <br> Important! Publish or Update your Layout to see the latest markup.
	 * @disabled 1
	 */
	public string $markup;
	/**
	 * @a-type textarea
	 * @label Custom Template
	 * @instructions Write your own template with full control over the HTML markup. <br> Copy the Default Template code and make your changes. <br><br> Check out our Docs to learn more about the <a target='_blank' href='https://docs.advanced-views.com/features/smart-templates'>available template engines</a>. <br><br> Press Ctrl (Cmd) + Alt + L to format the code. Press Ctrl + F to search (or replace).
	 */
	public string $custom_markup;
	/**
	 * @a-type select
	 * @label Template Engine
	 * @instructions Choose one of the <a target='_blank' href='https://docs.advanced-views.com/features/smart-templates'>supported template engines</a>, which will be used for this Layout.
	 * @default_value twig
	 */
	public string $template_engine;

	/**
	 * @a-type tab
	 * @label Styles & Scripts
	 */
	public bool $css_and_js_tab;
	/**
	 * @a-type textarea
	 * @label CSS Code
	 * @instructions Define your CSS style rules. <br> Rules defined here will be added within &lt;style&gt;&lt;/style&gt; tags ONLY to pages that have this View. <br><br> Press Ctrl (Cmd) + Alt + L to format the code; Ctrl + F to search/replace; Ctrl + Space for autocomplete. <br><br> Magic shortcuts are available: <br><br>  1. '#layout' as a unique instance selector, will be replaced with '.avf-layout--id--{x}' <br> 2. '#layout__' as a full element selector, so '#layout__element' will be replaced with '.avf-layout--id--{x} .avf-layout__element' <br> 3. '#this' as a short element selector, so '#this__element' will be replaced with '.avf-layout__element' <br> Note: all the shortcuts are compatible with the BEM name option.
	 */
	public string $css_code;
	/**
	 * @a-type textarea
	 * @label JavaScript code
	 * @instructions Configure libraries or add custom code to your Layout. <br><br> By default, the Layout is a <a target='_blank' href='https://docs.advanced-views.com/layouts/code-fields/javascript-code'>web component</a>, so this code will be executed once for every instance, and 'this', that refers to the current instance, is available. <br><br> If the Web Component Type is set to none, the js code here is plain, and can be used for any goals, including <a target='_blank' href='https://docs.advanced-views.com/layouts/component-options/interactivity-api'>WP Interactivity API</a>. <br><br> The code snippet will be added within &lt;script type='module'&gt;&lt;/script&gt; tags ONLY to pages that have this View. <br><br> Press Ctrl (Cmd) + Alt + L to format the code. Press Ctrl + F to search (or replace).
	 * @a-pro The field must be not required or have default value!
	 */
	public string $js_code;

	/**
	 * @a-type tab
	 * @label Output
	 */
	public bool $options_tab;
	/**
	 * @a-type select
	 * @label Register custom Gutenberg Block
	 * @instructions If a block vendor is selected then a <a target='_blank' href='https://docs.advanced-views.com/layouts/features/custom-gutenberg-blocks'>custom Gutenberg block</a> for this Layout will be available. <br><br> Note: this option is separate from the built-in Gutenberg integration block.
	 * @choices {"off":"Off","acf":"ACF Block","meta-box":"Meta Box Block","pods":"Pods Block"}
	 * @default_value off
	 * @a-pro The field must be not required or have default value!
	 */
	public string $gutenberg_block_vendor;
	/**
	 * @a-type true_false
	 * @label Render template when it's empty
	 * @instructions By default, if all the selected fields are empty, the Twig template won't be rendered. <br> Enable this option if you have specific logic inside the template and you want to render it even when all the fields are empty.
	 */
	public bool $is_render_when_empty;
	/**
	 * @a-type textarea
	 * @label Editorial description
	 * @instructions Add a short description for your Views’ purpose. <br> Note : This description is only seen on the admin Advanced Views list.
	 */
	public string $description;

	/**
	 * @a-type tab
	 * @label Developer
	 */
	public bool $developer_tab;
	/**
	 * @a-type textarea
	 * @label PHP Controller
	 * @instructions By customizing the PHP Controller instance you can add extra variables to the template and define the AJAX and REST API handlers. <a target='_blank' href='https://docs.advanced-views.com/layouts/code-fields/layout-controller'>Read more</a> <br> Press Ctrl (Cmd) + Alt + L to format the code. Press Ctrl + F to search (or replace).
	 */
	public string $php_variables;
	/**
	 * @a-type select
	 * @label Web Component Type
	 * @instructions By default, every Layout is a <a target='_blank' href='https://docs.advanced-views.com/layouts/code-fields/javascript-code'>web component</a>, which allows you to work easily with the element in the JS code field. <br><br> Set it to 'None' if you're going to use the <a target='_blank' href='https://docs.advanced-views.com/layouts/component-options/interactivity-api'>WP Interactivity API</a>.
	 * @choices {"classic":"Classic (no CSS isolation)","shadow_root_template":"Declarative Shadow DOM (CSS isolated, server-side)","shadow_dom":"JS Shadow DOM (CSS isolated, client-side)","none":"None"}
	 * @default_value classic
	 */
	public string $web_component;
	/**
	 * @a-type select
	 * @label Classes generation
	 * @instructions Controls classes generation in the Default Template.
	 * @choices {"bem":"BEM style","none":"None"}
	 * @default_value bem
	 */
	public string $classes_generation;
	/**
	 * @label BEM Unique Name
	 * @instructions Define a unique <a target='_blank' href='https://getbem.com/introduction/'>BEM name</a> for the element that will be used in the markup, or leave it empty to use the default ('avf-layout').
	 */
	public string $bem_name;
	/**
	 * @label CSS classes
	 * @instructions Add a class name without a dot (e.g. “class-name”) or multiple classes with single space as a delimiter (e.g. “class-name1 class-name2”). <br> These classes are added to the wrapping HTML element. <a target='_blank' href='https://www.w3schools.com/cssref/sel_class.asp'>Learn more about CSS Classes</a>.
	 */
	public string $css_classes;
	/**
	 * @a-type true_false
	 * @label Add classification classes to the markup
	 * @instructions By default, the field name is added as a prefix to all inner classes. For example, the image within the 'avatar' field will have the '__avatar-image' class. <br> Enabling this setting adds the generic class as well, such as '__image'. This feature can be useful if you want to apply styles based on field types.
	 */
	public bool $is_with_common_classes;
	/**
	 * @a-type true_false
	 * @label Do not skip unused wrappers
	 * @instructions By default, empty wrappers in the markup are skipped to optimize the output. For example, the '__row' wrapper will be skipped if there is no field label. <br> Enable this feature if you need all the wrappers in the output.
	 */
	public bool $is_with_unnecessary_wrappers;
	/**
	 * @a-type true_false
	 * @label Use the Post ID as the Layout ID in the markup
	 * @instructions Note: For backward compatibility purposes only. Enable this option if you have external CSS selectors that rely on outdated digital IDs.
	 */
	public bool $is_markup_with_digital_id;

	/**
	 * @a-type tab
	 * @label Source options
	 */
	public bool $source_tab;
	/**
	 * @a-type message
	 * @label Note
	 * @message The settings below affect only the generated shortcode. After saving and copying it, you can change them again: the same Layout can be loaded from various sources and have multiple shortcode versions.
	 */
	public string $source_message;
	/**
	 * @a-type select
	 * @label Source type
	 * @instructions 'Context-driven' takes the item(s) from the current context (the current page, user or loop item, the main query posts). 'Defined' sets the source explicitly in the shortcode.
	 * @choices {"context":"Context-driven","defined":"Defined"}
	 * @default_value context
	 */
	public string $source_type;
	/**
	 * @label Options page
	 * @instructions The options page slug (or ACF post id), the fields are loaded from.
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "custom"}]]
	 */
	public string $source_options_page;
	/**
	 * @a-type av_ajax_select
	 * @source post-selections
	 * @multiple 1
	 * @label Post Selections
	 * @instructions The posts of the selected Post Selections are displayed in the given order, without de-duplication. Can't be combined with the 'Posts' field.
	 * @var string[]
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "post"}, {"field": "local_acf_views_view__item-format","operator": "==","value": "multiple"}]]
	 */
	public array $source_post_selections;
	/**
	 * @a-type post_object
	 * @return_format id
	 * @multiple 1
	 * @label Posts
	 * @instructions Specific posts (Pages, CPT items, Products). Can't be combined with the 'Post Selections' field.
	 * @var int[]
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "post"}]]
	 */
	public array $source_posts;
	/**
	 * @a-type user
	 * @return_format id
	 * @multiple 1
	 * @label Users
	 * @instructions Specific users.
	 * @var int[]
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "user"}]]
	 */
	public array $source_users;
	/**
	 * @label Terms
	 * @instructions Specific taxonomy term IDs, delimited by a comma.
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "term"}]]
	 */
	public string $source_terms;
	/**
	 * @label Comment
	 * @instructions A specific comment ID.
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "comment"}]]
	 */
	public string $source_comments;
	/**
	 * @a-type select
	 * @multiple 1
	 * @ui 1
	 * @return_format value
	 * @label Menus
	 * @instructions Specific menus.
	 * @var string[]
	 * @conditional_logic [[{"field": "local_acf_views_view__source-type","operator": "==","value": "defined"}, {"field": "local_acf_views_view__item-type","operator": "==","value": "menu"}]]
	 */
	public array $source_menus;
	/**
	 * @a-type select
	 * @multiple 1
	 * @ui 1
	 * @return_format value
	 * @label Limit visibility only to roles
	 * @instructions The Layout is shown only to users with the selected roles. Leave empty for no limits.
	 * @var string[]
	 */
	public array $source_roles_with;
	/**
	 * @a-type select
	 * @multiple 1
	 * @ui 1
	 * @return_format value
	 * @label Hide only from roles
	 * @instructions The Layout is hidden from users with the selected roles. Leave empty for no limits.
	 * @var string[]
	 */
	public array $source_roles_without;

	/**
	 * @label With Gutenberg Block
	 * @instructions This field is deprecated.
	 * @a-pro The field must be not required or have default value!
	 * @a-deprecated IT'S INVISIBLE FIELD FOR BACK COMPATIBILITY ONLY
	 */
	public bool $is_has_gutenberg_block;
	/**
	 * @a-type true_false
	 * @label Use the Post ID in the Gutenberg block's name
	 * @instructions Note: For backward compatibility purposes only.
	 * @a-deprecated IT'S INVISIBLE FIELD FOR BACK COMPATIBILITY ONLY
	 */
	public bool $is_gutenberg_block_with_digital_id;

	/**
	 * @return array<string|int,mixed>
	 * @throws Exception
	 */
	public static function getGroupInfo(): array {
		return array_merge(
			parent::getGroupInfo(),
			array(
				'title' => __( 'View settings', 'acf-views' ),
			)
		);
	}

	// @phpcs:ignore
	protected static function getFieldInfo( string $fieldName ): ?FieldInfoInterface {
		// @phpcs:ignore
		$field_info = parent::getFieldInfo( $fieldName );

		if ( is_null( $field_info ) ) {
			return null;
		}

		switch ( $field_info->getName() ) {
			case self::FIELD_PHP_VARIABLES:
				$field_info->setArgument(
					'default_value',
					'<?php

declare(strict_types=1);
defined( "ABSPATH" ) || exit;

use Org\Wplake\Advanced_Views\Bridge\Controllers\Layout\Layout_Controller_Base;

return new class extends Layout_Controller_Base {
    /**
     * @return array<string,mixed>
     */
    public function get_variables(): array
    {
        return [
            // "custom_variable" => get_post_meta($this->get_object_id(), "your_field", true),
            // "another_var" => $this->get_custom_arguments()["another"] ?? "",
        ];
    }
    /**
     * @return array<string,mixed>
     */
    public function get_variables_for_validation(): array
    {
        // it\'s better to return dummy data here [ "custom_variable" => "dummy string", ]
        return $this->get_variables();
    }
    /**
     * @return array<string,mixed>
     */
    public function get_ajax_response(): array
	{
	    // $message = $this->get_container()->get(MyClass::class)->myMethod();
		return [
			// "message" => $message,
		];
	}
	/**
     * @return array<string,mixed>
     */
    public function get_rest_api_response(WP_REST_Request $request): array
	{
	    // $input = $request->get_json_params();
	    // $message = $this->get_container()->get(MyClass::class)->myMethod();
		return [
			// "message" => $message,
		];
	}
};
'
				);
				break;
		}

		return $field_info;
	}

	/**
	 * @return string[]
	 */
	public function get_used_meta_group_ids(): array {
		$field_groups = array();

		foreach ( $this->items as $item ) {
			$field_group = explode( '|', $item->field->key )[0];

			// ignore 'magic' groups.
			if ( 0 !== strpos( $field_group, '$' ) ) {
				$field_groups[] = $field_group;
			}

			foreach ( $item->repeater_fields as $repeater_field ) {
				$sub_field_group = explode( '|', $repeater_field->key )[0];

				// ignore 'magic' groups.
				if ( 0 !== strpos( $sub_field_group, '$' ) ) {
					$field_groups[] = $sub_field_group;
				}
			}
		}

		$field_groups = array_unique( $field_groups );

		return $field_groups;
	}

	public function get_css_code( string $mode ): string {
		$aliases  = array( 'view', self::MAGIC_CSS_SELECTOR );
		$css_code = $this->css_code;

		foreach ( $aliases as $alias ) {
			$css_code = $this->resolved_css_code( $css_code, $mode, $alias );
		}

		return $css_code;
	}

	public function is_classes_generation_enabled(): bool {
		return self::CLASS_GENERATION_NONE !== $this->classes_generation;
	}

	public function has_unique_bem_name(): bool {
		$bem_name = trim( $this->bem_name );

		return strlen( $bem_name ) > 0 &&
				! in_array( $bem_name, array( 'acf-view', Hard_Layout_Cpt::cpt_name() ), true );
	}

	/**
	 * @return array<string,string[]>
	 */
	public function get_multilingual_strings(): array {
		$strings = $this->get_multilingual_strings_from_fields();

		return $this->get_multilingual_strings_from_sub_fields( $strings );
	}

	/**
	 * @return array<string|int,mixed>
	 */
	public function get_exposed_post_fields(): array {
		$is_has_gutenberg_block = 'off' !== $this->gutenberg_block_vendor;

		return array_merge(
			parent::get_exposed_post_fields(),
			array(
				static::POST_FIELD_IS_HAS_GUTENBERG => $is_has_gutenberg_block ?
					static::POST_VALUE_IS_HAS_GUTENBERG :
					'',
			)
		);
	}

	public function get_bem_name(): string {
		$bem_name  = trim( $this->bem_name );
		$is_filled = strlen( $bem_name ) > 0;

		if ( $is_filled ) {
			$sanitized = self::sanitize_bem_name( $bem_name );

			return is_string( $sanitized ) ?
				$sanitized :
				Hard_Layout_Cpt::markup_name();
		}

		return Hard_Layout_Cpt::cpt_name();
	}


	public function get_item_class( string $suffix, Field_Settings $field_data ): string {
		if ( self::CLASS_GENERATION_NONE === $this->classes_generation ) {
			return '';
		}

		$classes = array();

		$classes[] = $this->get_bem_name() . '__' . $field_data->id . '-' . $suffix;

		if ( $this->is_with_common_classes ) {
			$classes[] = $this->get_bem_name() . '__' . $suffix;
		}

		return implode( ' ', $classes );
	}

	public function get_tag_name( string $prefix = '' ): string {
		return parent::get_tag_name( Hard_Layout_Cpt::markup_name() );
	}

	public function get_item_selector(
		Field_Settings $field,
		string $target,
		bool $is_inner_target = false,
		bool $is_skip_view = false
	): string {
		$markup_id = $this->get_markup_id();

		$selector = '';

		if ( ! $is_skip_view ) {
			$selector .= '' !== $this->bem_name ?
				'.' . $this->bem_name :
				sprintf(
					'.%s--id--%s',
					$this->get_bem_name(),
					$markup_id
				);
			$selector .= ' ';
		}

		$selector .= sprintf(
			'.%s__%s',
			esc_html( $this->get_bem_name() ),
			esc_html( $field->id )
		);

		// target can be empty, in case we need the field itself.
		if ( ( ! $this->is_with_unnecessary_wrappers &&
				'' === $field->label &&
				! $is_inner_target ) ||
			'' === $target ) {
			return $selector;
		}

		$selector = $this->is_with_common_classes ?
			sprintf(
				'%s .%s__%s',
				esc_html( $selector ),
				esc_html( $this->get_bem_name() ),
				$target
			) :
			sprintf(
				'%s .%s__%s-%s',
				esc_html( $selector ),
				esc_html( $this->get_bem_name() ),
				$field->id,
				$target
			);

		return $selector;
	}

	/**
	 * @return Field_Settings[]
	 */
	public function get_fields_with_view_link(): array {
		$fields_with_active_view_link = array();

		foreach ( $this->items as $item ) {
			if ( '' !== $item->field->acf_view_id ) {
				$fields_with_active_view_link[] = $item->field;
				continue;
			}

			foreach ( $item->repeater_fields as $repeater_field ) {
				if ( '' === $repeater_field->acf_view_id ) {
					continue;
				}

				$fields_with_active_view_link[] = $repeater_field;
			}
		}

		return $fields_with_active_view_link;
	}

	public function is_for_internal_usage_only(): bool {
		return '' !== $this->parent_field;
	}

	public function allows_empty_rendering(): bool {
		if ( $this->is_render_when_empty ) {
			return true;
		}

		// automatically allow rendering for custom-data Layouts.
		return 0 === count( $this->items ) &&
			$this->has_custom_markup();
	}

	/**
	 * @return array<string,string[]>
	 */
	protected function get_multilingual_strings_from_fields(): array {
		$labels = array();

		foreach ( $this->items as $item ) {
			if ( '' !== $item->field->label ) {
				$labels[] = $item->field->label;
			}
			if ( '' !== $item->field->link_label ) {
				$labels[] = $item->field->link_label;
			}
			if ( '' !== $item->field->map_marker_icon_title ) {
				$labels[] = $item->field->map_marker_icon_title;
			}
		}

		return array() !== $labels ?
			array(
				Plugin::get_theme_text_domain() => array_unique( $labels ),
			) :
			array();
	}

	/**
	 * @param array<string,string[]> $ml_strings
	 *
	 * @return array<string,string[]>
	 */
	protected function get_multilingual_strings_from_sub_fields( array $ml_strings ): array {
		$theme_text_domain                = Plugin::get_theme_text_domain();
		$ml_strings[ $theme_text_domain ] = $ml_strings[ $theme_text_domain ] ?? array();

		foreach ( $this->items as $item ) {
			foreach ( $item->repeater_fields as $repeater_field ) {
				if ( '' !== $repeater_field->label ) {
					$ml_strings[ $theme_text_domain ][] = $repeater_field->label;
				}

				if ( '' !== $repeater_field->link_label ) {
					$ml_strings[ $theme_text_domain ][] = $repeater_field->link_label;
				}

				if ( '' !== $repeater_field->map_marker_icon_title ) {
					$ml_strings[ $theme_text_domain ][] = $repeater_field->map_marker_icon_title;
				}
			}
		}

		$ml_strings[ $theme_text_domain ] = array_unique( $ml_strings[ $theme_text_domain ] );

		// do not keep empty.
		if ( array() === $ml_strings[ $theme_text_domain ] ) {
			unset( $ml_strings[ $theme_text_domain ] );
		}

		return $ml_strings;
	}
}
