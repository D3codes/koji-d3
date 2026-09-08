<?php
/** Run only in a disposable WordPress installation after activating Koji D3. */
function tabs_check( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	echo "PASS: $message\n";
}
function tabs_query( $args = array(), $tab = '', $main = true ) {
	$_GET['tab'] = $tab;
	$query = new WP_Query();
	if ( $main ) { $GLOBALS['wp_the_query'] = $query; $GLOBALS['wp_query'] = $query; }
	$query->query( array_merge( array( 'posts_per_page' => 2 ), $args ) );
	return $query;
}
$a = wp_create_category( 'Essays' );
$b = wp_create_category( 'Links' );
$c = wp_create_category( 'Reviews' );
$ids = array();
foreach ( array( array($a), array($b), array($a,$b), array($c), array($b), array($b) ) as $i => $cats ) {
	$ids[] = wp_insert_post( array( 'post_title' => 'Tab fixture ' . $i, 'post_status' => 'publish', 'post_category' => $cats ) );
}
stick_post( $ids[0] );
$tabs = array(
	array( 'id' => 'writing', 'label' => 'Writing', 'mode' => 'exclude', 'categories' => array($b) ),
	array( 'id' => 'links', 'label' => 'Links', 'mode' => 'include', 'categories' => array($b) ),
	array( 'id' => 'all', 'label' => 'Everything', 'mode' => 'all', 'categories' => array() ),
	array( 'id' => 'either', 'label' => 'Either', 'mode' => 'include', 'categories' => array($a,$c) ),
	array( 'id' => 'empty', 'label' => 'Empty', 'mode' => 'include', 'categories' => array(999999) ),
);
set_theme_mod( 'koji_d3_home_tabs', $tabs );
set_theme_mod( 'koji_d3_home_tabs_enabled', true );
$q = tabs_query( array( 'posts_per_page' => 100 ) );
tabs_check( ! array_intersect( array($ids[1],$ids[2],$ids[4],$ids[5]), wp_list_pluck($q->posts,'ID') ), 'Default excludes any post in excluded category' );
$q = tabs_query( array(), 'links' );
tabs_check( 4 === (int) $q->found_posts && 2 === (int) $q->max_num_pages && ! in_array($ids[0],wp_list_pluck($q->posts,'ID')), 'Inclusion count, pagination and sticky isolation' );
$first = wp_list_pluck($q->posts,'ID');
$q = tabs_query( array('paged'=>2), 'links' );
tabs_check( count($q->posts) === 2 && ! array_intersect($first,wp_list_pluck($q->posts,'ID')), 'Non-default page two has distinct matching posts' );
tabs_check( false !== strpos(get_pagenum_link(2),'tab=links'), 'Pagination retains selected tab' );
$q = tabs_query( array('posts_per_page'=>100), 'either' );
tabs_check( ! array_diff(array($ids[0],$ids[2],$ids[3]),wp_list_pluck($q->posts,'ID')) && 3 === (int)$q->found_posts, 'Multiple selected categories use OR' );
tabs_check( ! tabs_query(array(),'empty')->have_posts(), 'Deleted categories yield empty inclusion' );
$q = tabs_query(array(),'unknown');
tabs_check( $q->get('category__not_in') === array($b), 'Invalid ID falls back to default' );
tabs_check( false === strpos(get_pagenum_link(2),'tab='), 'Default pagination removes invalid tab ID' );
$q = tabs_query(array(),'all');
tabs_check( ! $q->get('category__in') && ! $q->get('category__not_in') && ! $q->get('ignore_sticky_posts'), 'All preserves normal category and sticky behavior' );
foreach ( array( array('feed'=>'rss2'), array('s'=>'Tab'), array('cat'=>$a), array('tag'=>'sample'), array('p'=>$ids[0]) ) as $args ) {
	$q = tabs_query($args,'links');
	tabs_check( ! $q->get('category__in'), 'Feed/search/archive/single isolation: ' . json_encode($args) );
}
$q = tabs_query(array(),'links',false);
tabs_check( ! $q->get('category__in'), 'Secondary query isolation' );
set_current_screen('edit-post');
$q = tabs_query(array(),'links');
tabs_check( ! $q->get('category__in'), 'Admin isolation' );
$GLOBALS['current_screen'] = null;
set_theme_mod('koji_d3_home_tabs_enabled',false);
tabs_check( ! tabs_query(array(),'links')->get('category__in') && ! koji_d3_home_tabs(), 'Disabled restores normal query' );
set_theme_mod('koji_d3_home_tabs_enabled',true);
set_theme_mod('koji_d3_home_tabs',array());
tabs_check( ! tabs_query(array(),'links')->get('category__in') && ! koji_d3_active_home_tab(), 'Empty configuration fallback' );
$dirty = array( array('id'=>'A<script>','label'=>'<b>Safe</b>','mode'=>'include','categories'=>array($a,'bad',array(1),-3)), array('id'=>'bad','label'=>'Bad','mode'=>'arbitrary'), array('id'=>'ascript','label'=>'Duplicate','mode'=>'all') );
$clean = koji_d3_sanitize_home_tabs($dirty);
tabs_check( count($clean) === 1 && $clean[0]['label'] === 'Safe' && $clean[0]['categories'] === array($a), 'Configuration sanitization and duplicate IDs' );
$chosen = $tabs;
$chosen[1]['default'] = true;
set_theme_mod('koji_d3_home_tabs', $chosen);
tabs_check( tabs_query()->get('category__in') === array($b), 'Checked non-first default controls homepage' );
tabs_check( tabs_query(array(),'unknown')->get('category__in') === array($b), 'Invalid URL uses checked default' );
tabs_check( false === strpos(get_pagenum_link(2),'tab='), 'Checked default has canonical pagination' );
tabs_query(array(),'writing');
tabs_check( false !== strpos(get_pagenum_link(2),'tab=writing'), 'First tab retains identifier when not default' );
set_theme_mod('koji_d3_home_tabs', array_reverse($chosen));
tabs_check( tabs_query()->get('category__in') === array($b), 'Reordering preserves checked default' );
$chosen[0]['default'] = true;
$sanitized = koji_d3_sanitize_home_tabs($chosen);
tabs_check( count(array_filter($sanitized, function($tab) { return $tab['default']; })) === 1, 'Sanitization permits only one default' );
set_theme_mod('koji_d3_home_tabs', array($tabs[0],$tabs[2]));
tabs_check( tabs_query()->get('category__not_in') === array($b), 'Removed default falls back safely' );
set_theme_mod('koji_d3_home_tabs',$tabs);
wp_delete_term($b,'category');
tabs_check( ! tabs_query(array(),'links')->have_posts(), 'Deleting every selected category is safe' );
set_theme_mod('koji_d3_home_tabs', array($tabs[3]));
wp_delete_term($c,'category');
tabs_check( tabs_query(array())->found_posts === 2, 'Remaining category survives deletion' );
$front = wp_insert_post(array('post_title'=>'Welcome','post_type'=>'page','post_status'=>'publish'));
$blog = wp_insert_post(array('post_title'=>'Blog','post_type'=>'page','post_status'=>'publish'));
update_option('show_on_front','page'); update_option('page_on_front',$front); update_option('page_for_posts',$blog);
tabs_check( koji_d3_home_tabs_url() === get_permalink($blog), 'Static front page uses configured posts page URL' );
tabs_check( ! tabs_query(array('page_id'=>$front),'either')->get('category__in'), 'Static front page is not filtered' );
update_option('show_on_front','posts');
set_theme_mod('koji_d3_home_tabs',array($tabs[3],$tabs[2]));
update_option('posts_per_page',1);
set_theme_mod('koji_pagination_type','scroll');
foreach ( new RecursiveIteratorIterator(new RecursiveDirectoryIterator(get_stylesheet_directory())) as $file ) {
	if ($file->isFile() && 'php' === $file->getExtension()) { token_get_all(file_get_contents($file->getPathname()), TOKEN_PARSE); }
}
echo "PASS: PHP syntax for every child-theme file\n";
define('REST_REQUEST', true);
tabs_check( ! tabs_query(array(),'either')->get('category__in'), 'REST query isolation' );
