<?php

declare( strict_types=1 );

namespace Org\Wplake\Advanced_Views\Post_Type\Types\Post_Selection\Acf\Groups;

defined( 'ABSPATH' ) || exit;

use Exception;
use Org\Wplake\Advanced_Views\Plugin\Cpt\Hard\Hard_Post_Selection_Cpt;
use Org\Wplake\Advanced_Views\Post_Query\Core\Settings\Query_Settings;
use Org\Wplake\Advanced_Views\Post_Type\Types\Layout\Acf\Groups\Field_Settings;
use Org\Wplake\Advanced_Views\Vendors\LightSource\AcfGroups\Interfaces\FieldInfoInterface;

class Post_Selection_Settings extends Templated_Selection_Settings implements Query_Settings {
	// to fix the group name in case the class name changes.
	const CUSTOM_GROUP_NAME = self::GROUP_NAME_PREFIX . 'acf-card-data';
	const LOCATION_RULES    = array(
		array(
			'post_type == ' . Hard_Post_Selection_Cpt::NAME,
		),
	);

	const FIELD_MARKUP                    = 'markup';
	const FIELD_CSS_CODE                  = 'css_code';
	const FIELD_JS_CODE                   = 'js_code';
	const FIELD_QUERY_PREVIEW             = 'query_preview';
	const FIELD_EXTRA_QUERY_ARGUMENTS     = 'extra_query_arguments';
	const FIELD_POST_TYPES                = 'post_types';
	const FIELD_POST_STATUSES             = 'post_statuses';
	const FIELD_ORDER_BY_META_FIELD_GROUP = 'order_by_meta_field_group';
	const FIELD_ORDER_BY_META_FIELD_KEY   = 'order_by_meta_field_key';
	const FIELD_CUSTOM_MARKUP             = 'custom_markup';
	const FIELD_ACF_VIEW_ID               = 'acf_view_id';
	const FIELD_BASIC_TAB                 = 'basic_tab';
	const FIELD_TEMPLATE_TAB              = 'template_tab';
	const FIELD_CSS_AND_JS_TAB            = 'css_and_js_tab';
	const FIELD_DEVELOPER_TAB             = 'developer_tab';

	const UNIQUE_ID_PREFIX = 'card_';

	const ITEMS_SOURCE_CONTEXT_POSTS = 'context_posts';

	/**
	 * @label Selection Name
	 * @required 1
	 * @instructions The internal name of the Post Selection, visible on the list screen.
	 * @a-html-class advanced-views__title
	 * @a-order 10
	 */
	public string $title;
	/**
	 * @a-type tab
	 * @label Query
	 * @a-order 20
	 */
	public bool $basic_tab;
	/**
	 * @label Source
	 * @a-type select
	 * @required 1
	 * @instructions 'Posts Query (WP_query)' will get posts based on the defined query, while 'Page Context (archive pages)' will get posts from where the Post Selection is inserted (e.g. archive, author or category).
	 * @choices {"posts_query":"Posts Query (WP_Query)","context_posts":"Page context (archive pages)"}
	 * @defeault_value custom_query
	 * @a-order 30
	 */
	public string $items_source;
	/**
	 * @a-type select
	 * @required 1
	 * @multiple 1
	 * @ui 1
	 * @label Post Type
	 * @instructions Filter by post type. Multiple types can be selected.
	 * @var string[]
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 40
	 */
	public array $post_types;
	/**
	 * @a-type select
	 * @required 1
	 * @multiple 1
	 * @ui 1
	 * @label Post Status
	 * @instructions Filter by post status. Multiple types can be selected.
	 * @default_value ["publish"]
	 * @var string[]
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 50
	 */
	public array $post_statuses;
	/**
	 * @required 1
	 * @label Maximum number of posts
	 * @instructions Use '-1' for unlimited.
	 * @default_value -1
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 60
	 */
	public int $limit;
	/**
	 * @a-type select
	 * @required 1
	 * @label Sort by
	 * @instructions Sort results by selecting an option. <br> 'Default' keeps the default order: latest first, while sticky flags may affect it.
	 * @choices {"none":"Default","ID":"ID","menu_order":"Menu order","meta_value":"Meta value","meta_value_num":"Meta value numeric","author":"Author","title":"Title","name":"Name","type":"Type","date":"Date","modified":"Modified","parent":"Parent","rand":"Random","comment_count":"Comment count","post__in":"Pool of posts"}
	 * @default_value none
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 70
	 */
	public string $order_by;
	/**
	 * @a-type select
	 * @return_format value
	 * @required 1
	 * @ui 1
	 * @label Sort by Meta Field Group
	 * @instructions Select a target group.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__order-by","operator": "==","value": "meta_value"}],[{"field": "local_acf_views_acf-card-data__order-by","operator": "==","value": "meta_value_num"}]]
	 * @a-order 80
	 */
	public string $order_by_meta_field_group;
	/**
	 * @a-type select
	 * @return_format value
	 * @label Sort by Meta Field
	 * @required 1
	 * @instructions Select a target field.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__order-by","operator": "==","value": "meta_value"}],[{"field": "local_acf_views_acf-card-data__order-by","operator": "==","value": "meta_value_num"}]]
	 * @a-order 90
	 */
	public string $order_by_meta_field_key;
	/**
	 * @a-type select
	 * @required 1
	 * @label Sort order
	 * @instructions Defines the sorting order of posts.
	 * @choices {"ASC":"Ascending","DESC":"Descending"}
	 * @default_value ASC
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 100
	 */
	public string $order;
	/**
	 * @a-type post_object
	 * @return_format id
	 * @label Pool of posts
	 * @multiple 1
	 * @instructions Manually assign specific posts to limit the query to. Only this pool of posts will then be considered for other filters. The 'Pool of posts' option can be selected to 'Sort by'.
	 * @var int[]
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 110
	 */
	public array $post_in;
	/**
	 * @a-type post_object
	 * @return_format id
	 * @label Exclude posts
	 * @instructions  Here you can manually exclude specific posts from the query. It means the query will ignore posts from this list, even if they fit the filters. Warning : this field can't be used together with 'Pool of posts'.
	 * @multiple 1
	 * @var int[]
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 120
	 */
	public array $post_not_in;
	/**
	 * @label Ignore Sticky Posts
	 * @instructions If unchecked then sticky posts will be at the top of results. <a target='_blank' href='https://wordpress.org/support/article/sticky-posts/'>Learn more about Sticky Posts</a>.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 130
	 */
	public bool $is_ignore_sticky_posts;
	/**
	 * @a-type textarea
	 * @label Query Preview
	 * @instructions For debugging purposes, this shows the query used to fetch posts for this Post Selection. Tip: Publish or update your Post Selection, then reload the page to view the latest query.
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 140
	 */
	public string $query_preview;

	/**
	 * @a-type tab
	 * @label Filters
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 150
	 */
	public bool $tax_filters_tab;
	/**
	 * @label Taxonomy Filters
	 * @a-no-tab 1
	 * @display seamless
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 160
	 */
	public Tax_Filter_Settings $tax_filter;

	/**
	 * @a-type tab
	 * @label Meta Filters
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 170
	 */
	public bool $meta_filters_tab;
	/**
	 * @a-no-tab 1
	 * @display seamless
	 * @conditional_logic [[{"field": "local_acf_views_acf-card-data__items-source","operator": "==","value": "posts_query"}]]
	 * @a-order 180
	 */
	public Meta_Filter_Settings $meta_filter;

	/**
	 * @a-type tab
	 * @label Display
	 * @a-order 190
	 */
	public bool $layout_tab;
	/**
	 * @a-type textarea
	 * @label Editorial description
	 * @instructions Add a short description for your Post Selection's purpose. Only seen on the admin Post Selections list.
	 * @a-order 230
	 */
	public string $description;




	/**
	 * @a-type tab
	 * @label Developer
	 * @a-order 380
	 */
	public bool $developer_tab;
	/**
	 * @a-type textarea
	 * @label PHP Controller
	 * @instructions By customizing the PHP Controller instance, you can add extra variables to the template, extra arguments to the <a target='_blank' href='https://developer.wordpress.org/reference/classes/wp_query/#parameters'>WP_Query instance</a>, and define the AJAX and REST API handlers. <a target='_blank' href='https://docs.advanced-views.com/post-selections/code-fields/selection-controller'>Read more</a> <br> Press Ctrl (Cmd) + Alt + L to format the code. Press Ctrl + F to search (or replace).
	 * @a-order 390
	 */
	public string $extra_query_arguments;

	// @phpcs:ignore
	protected static function getFieldInfo( string $fieldName ): ?FieldInfoInterface {
		// @phpcs:ignore
		$field_info = parent::getFieldInfo( $fieldName );

		if ( is_null( $field_info ) ) {
			return null;
		}

		switch ( $field_info->getName() ) {
			case self::FIELD_EXTRA_QUERY_ARGUMENTS:
				$field_info->setArgument(
					'default_value',
					'<?php

declare(strict_types=1);
defined( "ABSPATH" ) || exit;

use Org\Wplake\Advanced_Views\Bridge\Controllers\Selection\Selection_Controller_Base;

return new class extends Selection_Controller_Base {
    public function get_query_arguments(): array
    {
        // https://developer.wordpress.org/reference/classes/wp_query/#parameters
        return [
            // "author" => get_current_user_id(),
            // "post_parent" => $this->get_custom_arguments()["post_parent"] ?? 0,
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
	 * @return array<string|int,mixed>
	 * @throws Exception
	 */
	public static function getGroupInfo(): array {
		return array_merge(
			parent::getGroupInfo(),
			array(
				'title' => __( 'Card settings', 'acf-views' ),
			)
		);
	}

	/**
	 * @return string[]
	 */
	public function get_post_types(): array {
		return $this->post_types;
	}

	/**
	 * @return string[]
	 */
	public function get_post_statuses(): array {
		return $this->post_statuses;
	}

	public function is_ignore_sticky_posts(): bool {
		return $this->is_ignore_sticky_posts;
	}

	/**
	 * @return int[]
	 */
	public function get_post_in(): array {
		return $this->post_in;
	}

	/**
	 * @return int[]
	 */
	public function get_post_not_in(): array {
		return $this->post_not_in;
	}

	public function get_limit(): int {
		return $this->limit;
	}

	public function get_order(): string {
		return $this->order;
	}

	public function get_order_by(): string {
		return $this->order_by;
	}

	public function get_tax_filter(): Tax_Filter_Settings {
		return $this->tax_filter;
	}

	public function get_meta_filter(): Meta_Filter_Settings {
		return $this->meta_filter;
	}

	public function get_extra_query_arguments(): string {
		return $this->extra_query_arguments;
	}

	public function get_order_by_meta_acf_field_id(): string {
		return Field_Settings::get_field_id_by_key( $this->order_by_meta_field_key );
	}

	public function get_order_by_meta_field_key(): string {
		return $this->order_by_meta_field_key;
	}

	public function get_tag_name( string $prefix = '' ): string {
		return parent::get_tag_name( Hard_Post_Selection_Cpt::markup_name() );
	}
}
