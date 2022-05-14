//每一行的$buttons[] 代表一个功能，可以根据实际使用情况进行删减。
$buttons[] = 'fontselect';
$buttons[] = 'fontsizeselect';
$buttons[] = 'backcolor';
$buttons[] = 'underline';
$buttons[] = 'sub';
$buttons[] = 'sup';
$buttons[] = 'cleanup';
$buttons[] = 'wp_page';
return $buttons;
}
//数字2表示添加在编辑器的第二栏，如果改成3就是添加在第三栏
add_filter("mce_buttons_2", "add_editor_buttons");


//wp-clean-up
include ("wcu/wp-clean-up.php");


//vip可见
add_shortcode( 'vip_only', 'users_read_shortcode' );//注册短代码
function users_read_shortcode( $atts, $content = null ) {
    if ( is_user_logged_in() && !empty( $content ) && current_user_can( 'read' ) )    { 
       return '<div>
<span class="vip_only">'.$content.'</span></div>';
    }
   if ( !is_user_logged_in()){
      return '';
    }
   if ( is_user_logged_in() && !current_user_can( 'read' )){
       return '';
    }
}


//添加普通用户角色
add_role('pt_user', '普通用户', array(
'read' => false, //读权限
'edit_posts' => false,//编辑权限
'delete_posts' => false, //删除权限
));



//说说功能
function my_custom_shuoshuo_init() { 
	$labels = array( 
	'name' => '说说',
	'singular_name' => '说说', 
	'all_items' => '所有说说',
	'add_new' => '发表说说', 
	'add_new_item' => '撰写新说说',
	'edit_item' => '编辑说说', 
	'new_item' => '新说说', 
	'view_item' => '查看说说', 
	'search_items' => '搜索说说', 
	'not_found' => '暂无说说', 
	'not_found_in_trash' => '没有已遗弃的说说', 
	'parent_item_colon' => '',
	'menu_name' => '说说'
	); 
	$args = array( 
	'labels' => $labels, 
	'public' => true, 
	'publicly_queryable' => true, 
	'show_ui' => true, 
	'show_in_menu' => true, 
	'query_var' => true, 
	'rewrite' => true, 
	'capability_type' => 'post', 
	'has_archive' => true, 
	'hierarchical' => false, 
	'menu_position' => null, 
	'supports' => array('title','editor','author') 
	); 
	register_post_type('shuoshuo',$args); 
}
add_action('init', 'my_custom_shuoshuo_init'); 



//WordPress 后台媒体库显示文件的链接地址 
add_filter( 'media_row_actions', 'wpdaxue_media_row_actions', 10, 2 );
function wpdaxue_media_row_actions( $actions, $object ) {
	$actions['url'] = '<a href="'.wp_get_attachment_url( $object->ID ).'" target="_blank">图片地址</a>';
	return $actions;
}


//修改 WordPress 文件上传目录
function slider_upload_dir($uploads) {
    $siteurl = get_option( 'siteurl' );
    $uploads['path'] = WP_CONTENT_DIR . '/slider';
    $uploads['url'] = $siteurl . '/wp-content/slider';
    $uploads['subdir'] = '';
    $uploads['basedir'] = $uploads['path'];
    $uploads['baseurl'] = $uploads['url'];
    $uploads['error'] = false;
    return $uploads;
}
add_filter('upload_dir', 'slider_upload_dir');



//用户注册时间排序
        add_filter( 'manage_users_columns', 'my_users_columns' );
        function my_users_columns( $columns ){
            $columns[ 'registered' ] = '注册时间';
            return $columns;
        }
        
        add_action( 'manage_users_custom_column', 'output_my_users_columns', 10, 3 );
        function  output_my_users_columns( $var, $column_name, $user_id ){
            switch( $column_name ) {
                case "registered" :
                    return get_user_by('id', $user_id)->data->user_registered;
                break;
            }
        }
        
        add_filter( "manage_users_sortable_columns", 'wenshuo_users_sortable_columns' );
        function wenshuo_users_sortable_columns($sortable_columns){
            $sortable_columns['registered'] = 'registered';
            return $sortable_columns;
        }
        
        add_action( 'pre_user_query', 'wenshuo_users_search_order' );
        function wenshuo_users_search_order($obj){
            if(!isset($_REQUEST['orderby']) || $_REQUEST['orderby']=='registered' ){
                if( !in_array($_REQUEST['order'],array('asc','desc')) ){
                    $_REQUEST['order'] = 'desc';
                }
            $obj->query_orderby = "ORDER BY user_registered ".$_REQUEST['order']."";
            }
        }
        
        
        
// 在文章列表页与页面列表页添加缩略图列表
if ( !function_exists('fb_AddThumbColumn') && function_exists('add_theme_support') ) {
add_theme_support('post-thumbnails', array( 'post', 'page' ) );

function fb_AddThumbColumn($cols) {

$cols['thumbnail'] = __('Thumbnail');

return $cols;
}

function fb_AddThumbValue($column_name, $post_id) {

$width = (int) 35;
$height = (int) 35;

if ( 'thumbnail' == $column_name ) {
// thumbnail of WP 2.9
$thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
// image from gallery
$attachments = get_children( array('post_parent' => $post_id, 'post_type' => 'attachment', 'post_mime_type' => 'image') );
if ($thumbnail_id)
$thumb = wp_get_attachment_image( $thumbnail_id, array($width, $height), true );
elseif ($attachments) {
foreach ( $attachments as $attachment_id => $attachment ) {
$thumb = wp_get_attachment_image( $attachment_id, array($width, $height), true );
}
}
if ( isset($thumb) && $thumb ) {
echo $thumb;
} else {
echo __('None');
}
}
}
// 文章页调用
add_filter( 'manage_posts_columns', 'fb_AddThumbColumn' );
add_action( 'manage_posts_custom_column', 'fb_AddThumbValue', 10, 2 );
// 页面调用
add_filter( 'manage_pages_columns', 'fb_AddThumbColumn' );
add_action( 'manage_pages_custom_column', 'fb_AddThumbValue', 10, 2 );
}



//修改后台登录地址
add_action('login_enqueue_scripts','login_protection');
function login_protection(){
if($_GET['123'] != '456')header('Location:https://www.yxs2003.cn');
}



//图片添加alt属性和title信息
function image_alttitle( $imgalttitle ){
        global $post;
        $category = get_the_category();
        $flname=$category[0]->cat_name;
        $btitle = get_bloginfo();
        $imgtitle = $post->post_title;
        $imgUrl = "<img\s[^>]*src=(\"??)([^\" >]*?)\\1[^>]*>";
        if(preg_match_all("/$imgUrl/siU",$imgalttitle,$matches,PREG_SET_ORDER)){
                if( !empty($matches) ){
                        for ($i=0; $i < count($matches); $i++){
                                $tag = $url = $matches[$i][0];
                                $j=$i+1;
                                $judge = '/title=/';
                                preg_match($judge,$tag,$match,PREG_OFFSET_CAPTURE);
                                if( count($match) < 1 ) $altURL = ' alt="'.$imgtitle.' '.$flname.' 第'.$j.'张" title="'.$imgtitle.' '.$flname.' 第'.$j.'张-'.$btitle.'" '; $url = rtrim($url,'>');
                                $url .= $altURL.'>';
                                $imgalttitle = str_replace($tag,$url,$imgalttitle);
                        }
                }
        }
        return $imgalttitle;
}
add_filter( 'the_content','image_alttitle');



// 隐藏 姓，名 和 显示的名称，三个字段
add_action('show_user_profile','wpjam_edit_user_profile');
add_action('edit_user_profile','wpjam_edit_user_profile');
function wpjam_edit_user_profile($user){
	?>
	<script>
	jQuery(document).ready(function($) {
		$('#first_name').parent().parent().hide();
		$('#last_name').parent().parent().hide();
		$('#display_name').parent().parent().hide();
		$('.show-admin-bar').hide();
	});
	</script>
<?php
}
//更新时候，强制设置显示名称为昵称
add_action('personal_options_update','wpjam_edit_user_profile_update');
add_action('edit_user_profile_update','wpjam_edit_user_profile_update');
function wpjam_edit_user_profile_update($user_id){
	if (!current_user_can('edit_user', $user_id))
		return false;
	$user = get_userdata($user_id);
	$_POST['nickname']		= ($_POST['nickname'])?:$user->user_login;
	$_POST['display_name']	= $_POST['nickname'];
	$_POST['first_name']	= '';
	$_POST['last_name']		= '';
}



//插入图片时尺寸选择框只保留完整尺寸
add_filter('image_size_names_choose', 'wpjam_image_size_names_choose');
function wpjam_image_size_names_choose($image_sizes){
	unset($image_sizes['thumbnail']);
	unset($image_sizes['medium']);
	unset($image_sizes['large']);
	return $image_sizes;
}

// 禁用自动生成的图片尺寸
function shapeSpace_disable_image_sizes($sizes) {
	unset($sizes['thumbnail']);    // disable thumbnail size
	unset($sizes['medium']);       // disable medium size
	unset($sizes['large']);        // disable large size
	unset($sizes['medium_large']); // disable medium-large size
	unset($sizes['1536x1536']);    // disable 2x medium-large size
	unset($sizes['2048x2048']);    // disable 2x large size
	return $sizes;
}
add_action('intermediate_image_sizes_advanced', 'shapeSpace_disable_image_sizes');
 
// 禁用缩放尺寸
add_filter('big_image_size_threshold', '__return_false');
 
// 禁用其他图片尺寸
function shapeSpace_disable_other_image_sizes() {
	remove_image_size('post-thumbnail'); // disable images added via set_post_thumbnail_size() 
	remove_image_size('another-size');   // disable any other added image sizes
}
add_action('init', 'shapeSpace_disable_other_image_sizes');



//非管理员不允许进入后台
if ( is_admin() && ( !defined( 'DOING_AJAX' ) || !DOING_AJAX ) ) {
 $current_user = wp_get_current_user();
 if($current_user->roles[0] == get_option('default_role')) {
 wp_safe_redirect( home_url() );
 exit();
 }
}



//不存储评论者IP
add_filter( 'pre_comment_user_ip', 'zm_remove_comments_ip' );
function zm_remove_comments_ip( $comment_author_ip ) {
return '';
}



// 隐藏后台标题中的“WordPress”
add_filter('admin_title', 'zm_custom_admin_title', 10, 2);
	function zm_custom_admin_title($admin_title, $title){
		return $title.' &lsaquo; '.get_bloginfo('name');
}
add_filter('login_title', 'zm_custom_login_title', 10, 2);
	function zm_custom_login_title($login_title, $title){
		return $title.' &lsaquo; '.get_bloginfo('name');
}



//避免暴露管理员登录用户名
function lxtx_comment_body_class($content){ 
    $pattern = "/(.*?)([^>]*)author-([^>]*)(.*?)/i";
    $replacement = '$1$4';
    $content = preg_replace($pattern, $replacement, $content);  
    return $content;
}
add_filter('comment_class', 'lxtx_comment_body_class');
add_filter('body_class', 'lxtx_comment_body_class');


//修改url重写后的作者存档页的链接变量
add_filter('author_link', 'author_link', 10, 2);
function author_link( $link, $author_id) {
    global $wp_rewrite;
    $author_id = (int) $author_id;
    $link = $wp_rewrite->get_author_permastruct();
    if ( empty($link) ) {
        $file = home_url( '/' );
        $link = $file . '?author=' . $author_id;
    } else {
        $link = str_replace('%author%', $author_id, $link);
        $link = home_url( user_trailingslashit( $link ) );
    }
    return $link;
}

//替换作者的存档页的用户名，防止被其他用途
add_filter('request', 'author_link_request');
function author_link_request( $query_vars ) {
    if ( array_key_exists( 'author_name', $query_vars ) ) {
        global $wpdb;
        $author_id=$query_vars['author_name'];
        if ( $author_id ) {
            $query_vars['author'] = $author_id;
            unset( $query_vars['author_name'] );    
        }
    }
    return $query_vars;
}



//WordPress 评论通过审核后邮件通知评论人
add_action('comment_unapproved_to_approved', 'loper_comment_approved');
function loper_comment_approved($comment) {
  if(is_email($comment->comment_author_email)) {
    $post_link = get_permalink($comment->comment_post_ID);
    // 邮件标题，可自行更改
    $title = '您在 [' . get_option("blogname") . '] 的评论已通过审核';
 
    // 邮件内容
    $body = '
            <div style="background-color:#fff; border:1px solid #666666; color:#111; -moz-border-radius:8px; -webkit-border-radius:8px; -khtml-border-radius:8px;border-radius:8px; font-size:12px; width:702px; margin:0 auto; margin-top:10px;font-family:苹方,微软雅黑, Arial;">  
            <div style="background:#666666; width:100%; height:60px; color:white; -moz-border-radius:6px 6px 0 0; -webkit-border-radius:6px 6px 0 0; -khtml-border-radius:6px 6px 0 0; border-radius:6px 6px 0 0; ">
            <span style="height:60px; line-height:60px; margin-left:30px; font-size:12px;"> 您在 <a style="text-decoration:none; color:#00bbff;font-weight:600;"  href="' . get_option('home') . '">' . get_option('blogname') . '  </a> 的留言已通过作者审核并显示啦！</span></div>
            <div style="width:90%; margin:0 auto">
              <p><strong>' . trim(get_comment($comment)->comment_author) . '</strong>, 您好!</p>
              <p>您在 [' . get_option('blogname') . '] 的文章<strong>《' . get_the_title($comment->comment_post_ID) . '》</strong>上发表的评论已通过作者审核并显示，快来看看吧 ^_^:<br />
              <p>这是你的评论:</p>
              <p style="background-color: #EEE;border: 1px solid #DDD;padding: 20px;margin: 15px 0;">'. trim(get_comment($comment)->comment_content) . '</p>
              <p>您也可移步到文章《<a style="text-decoration:none; color:#00bbff" href="' . htmlspecialchars(get_comment_link($comment->comment_parent)) . '"> '. get_the_title($comment->comment_post_ID) .' </a>》查看你的评论</p>
              <p>欢迎再次光临 <a style="text-decoration:none; color:#00bbff" href="' . get_option('home') . '">' . get_option('blogname') . '</a></p>
              <p style="border-top:1px dashed #dbd1ce;"></p>
              <p>(此邮件由系统自动发出, 请勿回复。)</p>
              <p align="right">But如果您想更深入的和博主交流的话，欢迎回复哦^-^</p>
            </div></div>';
    $body = convert_smilies($body);//转换代码为表情
    @wp_mail($comment->comment_author_email, $title, $body, "Content-Type: text/html; charset=UTF-8");        
    }
}

//WordPress 评论回复邮件通知
 function comment_mail_notify($comment_id) {
      //$admin_notify = '1'; // admin 要不要收回复通知 ( '1'=要 ; '0'=不要 )
     $comment = get_comment($comment_id);//根据id获取这条评论相关数据
     $content=$comment->comment_content;
     //对评论内容进行匹配
     $match_count=preg_match_all('/<a href="#comment-([0-9]+)?" rel="nofollow">/si',$content,$matchs);
     if($match_count>0){//如果匹配到了
         foreach($matchs[1] as $parent_id){//对每个子匹配都进行邮件发送操作
             SimPaled_send_email($parent_id,$comment);
         }
     }elseif($comment->comment_parent!='0'){//以防万一，有人故意删了@回复，还可以通过查找父级评论id来确定邮件发送对象
         $parent_id=$comment->comment_parent;
         SimPaled_send_email($parent_id,$comment);
     }else return;
 }
add_action('comment_post', 'comment_mail_notify');
 
function SimPaled_send_email($parent_id,$comment){//发送邮件的函数
    $admin_email = get_bloginfo ('admin_email');//管理员邮箱
    $parent_comment=get_comment($parent_id);//获取被回复人（或叫父级评论）相关信息
    $author_email=$comment->comment_author_email;//评论人邮箱
    $to = trim($parent_comment->comment_author_email);//被回复人邮箱
    $spam_confirmed = $comment->comment_approved;
    if ($spam_confirmed != 'spam' && $to != $admin_email && $to != $author_email) {
           $wp_email = 'no-reply@' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME'])); // e-mail 发出点, no-reply 可改为可用的 e-mail.
           $subject = '您在 [' . get_option("blogname") . '] 的留言有了新回复';
           $message = '
            <div style="background-color:#fff; border:1px solid #666666; color:#111; -moz-border-radius:8px; -webkit-border-radius:8px; -khtml-border-radius:8px;border-radius:8px; font-size:12px; width:702px; margin:0 auto; margin-top:10px;font-family:苹方,微软雅黑, Arial;">  
            <div style="background:#666666; width:100%; height:60px; color:white; -moz-border-radius:6px 6px 0 0; -webkit-border-radius:6px 6px 0 0; -khtml-border-radius:6px 6px 0 0; border-radius:6px 6px 0 0; ">
            <span style="height:60px; line-height:60px; margin-left:30px; font-size:12px;"> 您在 <a style="text-decoration:none; color:#00bbff;font-weight:600;"  href="' . get_option('home') . '">' . get_option('blogname') . '  </a> 的留言有新回复啦！</span></div>
            <div style="width:90%; margin:0 auto">
              <p><strong>' . trim(get_comment($parent_id)->comment_author) . '</strong>, 您好!</p>
              <p>您在 [' . get_option('blogname') . '] 的文章<strong>《' . get_the_title($comment->comment_post_ID) . '》</strong>上发表的评论有新回复啦，快来看看吧 ^_^:<br />
              <p>这是你的评论:</p>
              <p style="background-color: #EEE;border: 1px solid #DDD;padding: 20px;margin: 15px 0;">'. trim(get_comment($parent_id)->comment_content) . '</p>
              <p><strong>' . trim($comment->comment_author) . '</strong> 给你的回复是:<br />
              <p style="background-color: #EEE;border: 1px solid #DDD;padding: 20px;margin: 15px 0;">'. trim($comment->comment_content) . '</p>
              <p>您也可移步到文章《<a style="text-decoration:none; color:#00bbff" href="' . htmlspecialchars(get_comment_link($comment->comment_parent)) . '"> '. get_the_title($comment->comment_post_ID) .' </a>》查看完整回复内容</p>
              <p>欢迎再次光临 <a style="text-decoration:none; color:#00bbff" href="' . get_option('home') . '">' . get_option('blogname') . '</a></p>
              <p style="border-top:1px dashed #dbd1ce;"></p>
              <p>(此邮件由系统自动发出, 请勿回复。)</p>
              <p align="right">But如果您想更深入的和博主交流的话，欢迎回复哦^-^</p>
            </div></div>';
        $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
        $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
        $message = convert_smilies($message);//转换代码为表情
        wp_mail( $to, $subject, $message, $headers );
    }
}



//删除 wp_head 中无关紧要的代码
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'start_post_rel_link');
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'adjacent_posts_rel_link');

//移除 WordPress 的 Admin Bar
add_filter( 'show_admin_bar', '__return_false' );

//移除自动修正 WordPress 大小写函数
remove_filter( 'the_content', 'capital_P_dangit' );
remove_filter( 'the_title', 'capital_P_dangit' );
remove_filter( 'comment_text', 'capital_P_dangit' );

//禁用所有文章类型的修订版本
add_filter( 'wp_revisions_to_keep', 'fanly_wp_revisions_to_keep', 10, 2 );
function fanly_wp_revisions_to_keep( $num, $post ) { return 0;}

//禁用自动保存
add_action('wp_print_scripts', 'fanly_no_autosave');
function fanly_no_autosave() { wp_deregister_script('autosave'); }

//移除WP为仪表盘(dashboard)页面加载的小工具
function cwp_remove_dashboard_widgets() {
    global $wp_meta_boxes;
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}
add_action('wp_dashboard_setup', 'cwp_remove_dashboard_widgets',11 );

//WP前台顶部清理 
function cwp_header_clean_up(){
    if (!is_admin()) {
        foreach(array('wp_generator','rsd_link','index_rel_link','start_post_rel_link','wlwmanifest_link') as $clean){remove_action('wp_head',$clean);}
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        remove_action( 'wp_head', 'feed_links', 2 );
        remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
        remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
        remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
        foreach(array('single_post_title','bloginfo','wp_title','category_description','list_cats','comment_author','comment_text','the_title','the_content','the_excerpt') as $where){
         remove_filter ($where, 'wptexturize');
        }
        /*remove_filter( 'the_content', 'wpautop' );
        remove_filter( 'the_excerpt', 'wpautop' );*/
        wp_deregister_script( 'l10n' );
    }
}

// 隐藏左上角WordPress标志
function hidden_admin_bar_remove() {
    global $wp_admin_bar;
    $wp_admin_bar->remove_menu('wp-logo');
}
add_action('wp_before_admin_bar_render', 'hidden_admin_bar_remove', 0);

