<?php

//リンクのないカテゴリーの取得（複数）
if ( !function_exists( 'get_the_nolink_categories' ) ):
function get_the_nolink_categories(){
  $categories = null;
  foreach((get_the_category()) as $category){
    $categories .= '<span class="entry-category">'.$category->cat_name.'</span>';
  }
  return $categories;
}

endif;


//リンクのないカテゴリーの出力（複数）
if ( !function_exists( 'the_nolink_categories' ) ):
function the_nolink_categories(){
  echo get_the_nolink_categories();
}
endif;



//カテゴリリンクの取得
if ( !function_exists( 'get_the_category_links' ) ):
function get_the_category_links(){
  $categories = null;
  foreach((get_the_category()) as $category){
    $categories .= '<a class="catlink catlink-'.$category->cat_ID.'" href="'.get_category_link( $category->cat_ID ).'">'.$category->cat_name.'</a>';
  }
  return $categories;
}
endif;


//カテゴリリンクの出力
if ( !function_exists( 'the_category_links' ) ):
function the_category_links(){
  echo get_the_category_links();
}
endif;


//リンクのないカテゴリーの取得
if ( !function_exists( 'get_the_nolink_category' ) ):
function get_the_nolink_category(){
  $categories = get_the_category();
  //var_dump($categories);
  if ( isset($categories[0]) ) {
    $category = $categories[0];
    return '<span class="category-label category-label-'.$category->cat_ID.'">'.$category->cat_name.'</span>';
  }
}
endif;


//リンクのないカテゴリーの出力
if ( !function_exists( 'the_nolink_category' ) ):
function the_nolink_category(){
  echo get_the_nolink_category();
}

endif;


//タグリンクの取得
if ( !function_exists( 'get_the_tag_links' ) ):
function get_the_tag_links(){
  $tags = null;
  $posttags = get_the_tags();
  if ( $posttags ) {
    foreach(get_the_tags() as $tag){
      $tags .= '<a class="taglink taglink-'.$tag->term_id.'" href="'.get_tag_link( $tag->term_id ).'">'.$tag->name.'</a>';
    }
  }
  return $tags;
}
endif;


//タグリンクの出力
if ( !function_exists( 'the_tag_links' ) ):
function the_tag_links(){
  echo get_the_tag_links();
}
endif;


//コメントが許可されているか
if ( !function_exists( 'is_comment_allow' ) ):
function is_comment_allow(){
  global $post;
  if ( isset($post->comment_status) ) {
    return $post->comment_status == 'open';
  }
  return false;
}
endif;


//現在のカテゴリをカンマ区切りテキストで取得する
if ( !function_exists( 'get_category_ids' ) ):
function get_category_ids(){
  if ( is_single() ) {//投稿ページでは全カテゴリー取得
    $categories = get_the_category();
    $category_IDs = array();
    foreach($categories as $category):
      array_push( $category_IDs, $category -> cat_ID);
    endforeach ;
    return $category_IDs;
  } elseif ( is_category() ) {//カテゴリページではトップカテゴリーのみ取得
    $categories = get_the_category();
    $cat_now = $categories[0];
    return array( $cat_now->cat_ID );
  }
  return null;
}
endif;


//AdSense用のフォーマットに変換
if ( !function_exists( 'to_adsense_format' ) ):
function to_adsense_format($format){
  switch ($format) {
    case DATA_AD_FORMAT_AUTO:
      $format = DATA_AD_FORMAT_AUTO;
      break;
    case DATA_AD_FORMAT_RECTANGLE:
      $format = DATA_AD_FORMAT_RECTANGLE;
      break;
    case DATA_AD_FORMAT_HORIZONTAL:
      $format = DATA_AD_FORMAT_HORIZONTAL;
      break;
    case DATA_AD_FORMAT_VERTICAL:
      $format = DATA_AD_FORMAT_VERTICAL;
      break;
    default:
      $format = DATA_AD_FORMAT_RECTANGLE;
      break;
  }
  return $format;
}
endif;

//フォーマットを指定して広告テンプレートファイル呼び出す
if ( !function_exists( 'get_template_part_with_ad_format' ) ):
function get_template_part_with_ad_format($format = DATA_AD_FORMAT_AUTO, $wrap_class = null, $label_visible = 1){
  // if ($wrap_class) {
  //   echo '<div class="'.$wrap_class.'">';
  // }
  if (isset($wrap_class)) {
    $wrap_class = ' '.trim($wrap_class).' ad-'.$format;
  }
  if ($label_visible) {
    $wrap_class .= ' ad-label-visible';
  } else {
    $wrap_class .= ' ad-label-invisible';
  }
  //var_dump($format);
  //$format変数をテンプレートファイルに渡す
  set_query_var('format', $format);
  //$format変数をテンプレートファイルに渡す
  set_query_var('wrap_class', $wrap_class);
  //広告テンプレートの呼び出し
  get_template_part('tmp/ad');
  // if ($wrap_class) {
  //   echo '</div>';
  // }
}
endif;


//オプション付きのテンプレート呼び出し
if ( !function_exists( 'get_template_part_with_option' ) ):
function get_template_part_with_option($slug, $option = null){
  //$option変数をテンプレートファイルに渡す
  set_query_var('option', $option);
  //広告テンプレートの呼び出し
  get_template_part($slug);
}
endif;

//オプションの値をデータベースに保存する
if ( !function_exists( 'update_theme_option' ) ):
function update_theme_option($option_name){
  $opt_val = isset($_POST[$option_name]) ? $_POST[$option_name] : '';
  //update_option($option_name, $opt_val);
  set_theme_mod($option_name, $opt_val);
}
endif;

//オプションの値をデータベースから取得する
if ( !function_exists( 'get_theme_option' ) ):
function get_theme_option($option_name, $default = null){
  return get_theme_mod($option_name, $default);
}
endif;

//highlight-jsのCSS URLを取得
if ( !function_exists( 'get_highlight_js_css_url' ) ):
function get_highlight_js_css_url(){
  return get_template_directory_uri() . '/plugins/highlight-js/styles/'.get_code_highlight_style().'.css';
}
endif;


//ソースコードのハイライト表示に必要なリソースの読み込み
if ( !function_exists( 'wp_enqueue_highlight_js' ) ):
function wp_enqueue_highlight_js(){
  //global $pagenow;
  if ( (is_code_highlight_enable() && is_singular()) || is_admin_php_page() ) {
    // if (is_admin()) {
    //   echo '<link rel="stylesheet" type="text/css" href="'. get_highlight_js_css_url().'">'.PHP_EOL;
    // } else {
    //   wp_enqueue_style( 'code-highlight-style',  get_highlight_js_css_url() );
    // }

    //ソースコードハイライト表示用のスタイル
    wp_enqueue_style( 'code-highlight-style',  get_highlight_js_css_url() );
    wp_enqueue_script( 'code-highlight-js', get_template_directory_uri() . '/plugins/highlight-js/highlight.min.js', array( 'jquery' ), false, true );
    if (is_admin_php_page()) {
      $selector = '.entry-content pre';
    } else {
      $selector = get_code_highlight_css_selector();
    }

    $data = minify_js('
          (function($){
           $("'.$selector.'").each(function(i, block) {
            hljs.highlightBlock(block);
           });
          })(jQuery);
        ');
    wp_add_inline_script( 'code-highlight-js', $data, 'after' ) ;
  }
}
endif;

// //LightboxプラグインURLの取得
// if ( !function_exists( 'get_lightbox_css_url' ) ):
// function get_lightbox_css_url(){
//   return get_template_directory_uri() . '/plugins/lightbox2/dist/css/lightbox.min.css';
// }
// endif;

// //画像ズームエフェクト用のスタイルタグを出力（管理画面用）
// if ( !function_exists( 'the_zoom_effect_link_tag' ) ):
// function the_zoom_effect_link_tag(){
//   if (is_admin_php_page()) {
//     if (is_lightbox_effect_enable()) {
//       echo '<link rel="stylesheet" href="'.get_lightbox_css_url().'" type="text/css" />';
//     }
//   }

// }
// endif;

//Lightboxの読み込み
if ( !function_exists( 'wp_enqueue_lightbox' ) ):
function wp_enqueue_lightbox(){
 if ( ((is_lightbox_effect_enable() && is_singular()) || is_admin_php_page()) ) {
    //Lightboxスタイルの呼び出し
    wp_enqueue_style( 'lightbox-style', get_template_directory_uri() . '/plugins/lightbox2/dist/css/lightbox.min.css' );
    //Lightboxスクリプトの呼び出し
    wp_enqueue_script( 'lightbox-js', get_template_directory_uri() . '/plugins/lightbox2/dist/js/lightbox.min.js', array( 'jquery' ), false, true  );
  }
}
endif;

//lityの読み込み
if ( !function_exists( 'wp_enqueue_lity' ) ):
function wp_enqueue_lity(){
 if ( ((is_lity_effect_enable() && is_singular()) || is_admin_php_page()) ) {
    //lityスタイルの呼び出し
    wp_enqueue_style( 'lity-style', get_template_directory_uri() . '/plugins/lity/dist/lity.min.css' );
    //lityスクリプトの呼び出し
    wp_enqueue_script( 'lity-js', get_template_directory_uri() . '/plugins/lity/dist/lity.min.js', array( 'jquery' ), false, true  );
  }
}
endif;


//baguetteboxの読み込み
if ( !function_exists( 'wp_enqueue_baguettebox' ) ):
function wp_enqueue_baguettebox(){
 if ( ((is_baguettebox_effect_enable() && is_singular()) || is_admin_php_page()) ) {
    //baguetteboxスタイルの呼び出し
    wp_enqueue_style( 'baguettebox-style', get_template_directory_uri() . '/plugins/baguettebox/dist/baguetteBox.min.css' );
    //baguetteboxスクリプトの呼び出し
    wp_enqueue_script( 'baguettebox-js', get_template_directory_uri() . '/plugins/baguettebox/dist/baguetteBox.min.js', array( 'jquery' ), false, true  );
    if (is_singular()) {
      $selector = '.entry-content';
    } else {
      $selector = '.entry-demo';
    }
    $data = minify_js('
          (function($){
           baguetteBox.run("'.$selector.'");
          })(jQuery);
        ');
    wp_add_inline_script( 'baguettebox-js', $data, 'after' ) ;

  }
}
endif;


//clingifyの読み込み
if ( !function_exists( 'wp_enqueue_clingify' ) ):
function wp_enqueue_clingify(){
  $browser_info = get_browser_info();
  $is_ie = $browser_info['browser_name'] == 'IE';
  $is_edge_version_under_16 = ($browser_info['browser_name'] == 'IE') && (intval($browser_info['browser_version']) < 16);
  //グローバルナビ追従が有効な時
  if ( is_global_navi_fixed() || is_scrollable_sidebar_enable() ) {
    //clingifyスタイルの呼び出し
    //wp_enqueue_style( 'clingify-style', get_template_directory_uri() . '/plugins/clingify/clingify.css' );
    //clingifyスクリプトの呼び出し
    wp_enqueue_script( 'clingify-js', get_template_directory_uri() . '/plugins/clingify/jquery.clingify.min.js', array( 'jquery' ), false, true  );
    if (is_global_navi_fixed()) {
      switch (get_header_layout_type()) {
        case 'center_logo':
          $selector = '.navi';
          break;
        default:
          $selector = '.header-container';
          break;
      }
      //$selector = '.sidebar-scroll';
      $data = minify_js('
              (function($){
               $("'.$selector.'").clingify();
              })(jQuery);
            ');
      wp_add_inline_script( 'clingify-js', $data, 'after' );
    }

    //position: sticky;に対応していないブラウザの場合はclingifyを実行
    if (is_scrollable_sidebar_enable() && ($is_ie || $is_edge_version_under_16)) {
      $data = minify_js('
              (function($){
               $(".sidebar-scroll").clingify();
              })(jQuery);
            ');
      wp_add_inline_script( 'clingify-js', $data, 'after' );
    }
    if (is_scrollable_main_enable() && ($is_ie || $is_edge_version_under_16)) {
      $data = minify_js('
              (function($){
               $(".main-scroll").clingify();
              })(jQuery);
            ');
      wp_add_inline_script( 'clingify-js', $data, 'after' );
    }

  }
}
endif;


//Slickの読み込み
if ( !function_exists( 'wp_enqueue_slick' ) ):
function wp_enqueue_slick(){
 if ( 1 ) {
    //clingifyスタイルの呼び出し
    //wp_enqueue_style( 'slick-style', get_template_directory_uri() . '/plugins/slick/slick.css' );
    wp_enqueue_style( 'slick-theme-style', get_template_directory_uri() . '/plugins/slick/slick-theme.css' );
    //baguetteboxスクリプトの呼び出し
    wp_enqueue_script( 'slick-js', get_template_directory_uri() . '/plugins/slick/slick.min.js', array( 'jquery' ), false, true  );
    $data = minify_js('
              (function($){
               $(".carousel-content").slick({
  infinite: true,
  slidesToShow: 6,
  slidesToScroll: 6
});
              })(jQuery);
            ');
    wp_add_inline_script( 'slick-js', $data, 'after' ) ;

  }
}
endif;


//Google Fontsの読み込み
if ( !function_exists( 'wp_enqueue_google_fonts' ) ):
function wp_enqueue_google_fonts(){
  if (!is_site_font_family_local()) {
    wp_enqueue_style( 'google-fonts-'.get_site_font_source(), get_site_font_source_url() );
  }
}
endif;


//設定変更CSSを読み込む
if ( !function_exists( 'wp_add_css_custome_to_inline_style' ) ):
function wp_add_css_custome_to_inline_style(){
  ob_start();//バッファリング
  get_template_part('tmp/css-custom');
  $css_custom = ob_get_clean();
  //CSSの縮小化
  $css_custom = minify_css($css_custom);
  //HTMLにインラインでスタイルを書く
  wp_add_inline_style( 'font-awesome-style', $css_custom );
}
endif;


//投稿を1つランダム取得
if ( !function_exists( 'get_random_posts' ) ):
function get_random_posts($count = 1){
  $count = intval($count);
  $posts = get_posts('numberposts='.$count.'&orderby=rand');
  if ($couut == 1) {
    foreach( $posts as $post ) {
      return $post;
    }
  } else {
    return $posts;
  }
}
endif;


//最新の投稿を取得
if ( !function_exists( 'get_latest_posts' ) ):
function get_latest_posts($count = 1){
  $count = intval($count);
  $posts = get_posts('numberposts='.$count);
  if ($couut == 1) {
    foreach( $posts as $post ) {
      return $post;
    }
  } else {
    return $posts;
  }
}
endif;



//更新日の取得（更新日がない場合はnullを返す）
if ( !function_exists( 'get_update_time' ) ):
function get_update_time($format = 'Y.m.d') {
  $mtime = get_the_modified_time('Ymd');
  $ptime = get_the_time('Ymd');
  if ($ptime > $mtime) {
    return get_the_time($format);
  } elseif ($ptime === $mtime) {
    return null;
  } else {
    return get_the_modified_time($format);
  }
}
endif;



//サイトアドレスが含まれているか
if ( !function_exists( 'includes_site_url' ) ):
function includes_site_url($url){
  //URLにサイトアドレスが含まれていない場合
  if (strpos($url, site_url()) === false) {
    return false;
  } else {
    return true;
  }
}
endif;


//内部URLをローカルパスに変更
if ( !function_exists( 'url_to_local' ) ):
function url_to_local($url){
  //URLにサイトアドレスが含まれていない場合
  if (!includes_site_url($url)) {
    return false;
  }
  $path = str_replace(content_url(), WP_CONTENT_DIR, $url);
  $path = str_replace('\\', '/', $path);
  return $path;
}
endif;


//画像URLから幅と高さを取得する（同サーバー内ファイルURLのみ）
if ( !function_exists( 'get_image_width_and_height' ) ):
function get_image_width_and_height($image_url){
  //URLにサイトアドレスが含まれていない場合
  if (!includes_site_url($image_url)) {
    return false;
  }
  $wp_upload_dir = wp_upload_dir();
  $uploads_dir = $wp_upload_dir['basedir'];
  $uploads_url = $wp_upload_dir['baseurl'];
  $image_file = str_replace($uploads_url, $uploads_dir, $image_url);
  $imagesize = getimagesize($image_file);
  if ($imagesize) {
    $res = array();
    $res['width'] = $imagesize[0];
    $res['height'] = $imagesize[1];
    return $res;
  }
}
endif;

if ( !function_exists( 'is_admin_php_page' ) ):
function is_admin_php_page(){
  global $pagenow;
  return $pagenow == 'admin.php';
}
endif;



//サイトのドメインを取得
if ( !function_exists( 'get_the_site_domain' ) ):
function get_the_site_domain(){
  // // //ドメイン情報を$results[1]に取得する
  // preg_match( '/https?:\/\/(.+?)\//i', admin_url(), $results );
  // return $results[1];
  return get_domain_name(home_url());
}
endif;



//URLからドメインを取得
if ( !function_exists( 'get_domain_name' ) ):
function get_domain_name($url){
  return parse_url($url, PHP_URL_HOST);
}
endif;


//拡張子のみを取得する
if ( !function_exists( 'get_extention' ) ):
function get_extention($filename){
  return preg_replace('/^.*\.([^.]+)$/D', '$1', $filename);
}
endif;


//ファイル名のみを取得する
if ( !function_exists( 'get_basename' ) ):
function get_basename($filename){
  $p = pathinfo($filename);
  return basename ( $filename, ".{$p['extension']}" );
}
endif;

//bbPressのページかどうか
if ( !function_exists( 'is_bbpress_page' ) ):
function is_bbpress_page(){
  if (function_exists('bbp_is_topic')) {
    if (bbp_is_topic() ||
        bbp_is_forum() ||
        bbp_is_forum_archive() ||
        bbp_is_single_forum() ||
        bbp_is_forum_edit() ||
        bbp_is_single_topic() ||
        bbp_is_topic_archive() ||
        bbp_is_topic_edit() ||
        bbp_is_topic_tag() ||
        bbp_is_topic_tag_edit() ||
        bbp_is_reply()||
        bbp_is_reply_edit() ||
        bbp_is_single_reply() ||
        bbp_is_favorites() ||
        bbp_is_subscriptions()) {
      return true;
    }
  }
}
endif;


//子テーマが存在するか
if ( !function_exists( 'is_child_theme_exists' ) ):
function is_child_theme_exists(){
  return get_template_directory_uri() != get_stylesheet_directory_uri();
}
endif;

//スクロール追従領域が有効化
if ( !function_exists( 'is_scrollable_sidebar_enable' ) ):
function is_scrollable_sidebar_enable(){
  return is_active_sidebar('sidebar-scroll');
}
endif;

//スクロール追従領域が有効化
if ( !function_exists( 'is_scrollable_main_enable' ) ):
function is_scrollable_main_enable(){
  return is_active_sidebar('main-scroll');
}
endif;

//ブラウザ情報の取得
//http://web-pixy.com/php-device-browser/
if ( !function_exists( 'get_browser_info' ) ):
function get_browser_info(){

  $ua = $_SERVER['HTTP_USER_AGENT'];
  $browser_name = $browser_version = $webkit_version = $platform = NULL;
  $is_webkit = false;

  //Browser
  if(preg_match('/Edge/i', $ua)){

    $browser_name = 'Edge';

    if(preg_match('/Edge\/([0-9.]*/', $ua, $match)){

      $browser_version = $match[1];
    }

  }elseif(preg_match('/(MSIE|Trident)/i', $ua)){

    $browser_name = 'IE';

    if(preg_match('/MSIE\s([0-9.]*)/', $ua, $match)){

      $browser_version = $match[1];

    }elseif(preg_match('/Trident\/7/', $ua, $match)){

      $browser_version = 11;
    }

  }elseif(preg_match('/Presto|OPR|OPiOS/i', $ua)){

    $browser_name = 'Opera';

    if(preg_match('/(Opera|OPR|OPiOS)\/([0-9.]*)/', $ua, $match)) $browser_version = $match[2];

  }elseif(preg_match('/Firefox/i', $ua)){

    $browser_name = 'Firefox';

    if(preg_match('/Firefox\/([0-9.]*)/', $ua, $match)) $browser_version = $match[1];

  }elseif(preg_match('/Chrome|CriOS/i', $ua)){

    $browser_name = 'Chrome';

    if(preg_match('/(Chrome|CriOS)\/([0-9.]*)/', $ua, $match)) $browser_version = $match[2];

  }elseif(preg_match('/Safari/i', $ua)){

    $browser_name = 'Safari';

    if(preg_match('/Version\/([0-9.]*)/', $ua, $match)) $browser_version = $match[1];
  }

  //Webkit
  if(preg_match('/AppleWebkit/i', $ua)){

    $is_webkit = true;

    if(preg_match('/AppleWebKit\/([0-9.]*)/', $ua, $match)) $webkit_version = $match[1];
  }

  //Platform
  if(preg_match('/ipod/i', $ua)){

    $platform = 'iPod';

  }elseif(preg_match('/iphone/i', $ua)){

    $platform = 'iPhone';

  }elseif(preg_match('/ipad/i', $ua)){

    $platform = 'iPad';

  }elseif(preg_match('/android/i', $ua)){

    $platform = 'Android';

  }elseif(preg_match('/windows phone/i', $ua)){

    $platform = 'Windows Phone';

  }elseif(preg_match('/linux/i', $ua)){

    $platform = 'Linux';

  }elseif(preg_match('/macintosh|mac os/i', $ua)) {

    $platform = 'Mac';

  }elseif(preg_match('/windows/i', $ua)){

    $platform = 'Windows';
  }

  return array(

    'ua' => $ua,
    'browser_name' => $browser_name,
    'browser_version' => intval($browser_version),
    'is_webkit' => $is_webkit,
    'webkit_version' => intval($webkit_version),
    'platform' => $platform
  );
}//get_browser_info()
endif;


//サイトフォントソースコードの取得
if ( !function_exists( 'get_site_font_source' ) ):
function get_site_font_source(){
  $font_source = get_site_font_family();
  //空白を取り除く
  $font_source = str_replace('_', '', $font_source);
  //大文字を小文字に
  $font_source = strtolower($font_source);
  return $font_source;
}
endif;


//サイトフォントソースコードURLの取得
if ( !function_exists( 'get_site_font_source_url' ) ):
function get_site_font_source_url(){
  return 'https://fonts.googleapis.com/earlyaccess/'.get_site_font_source().'.css';
}
endif;

//カラーコードをRGBに変換
if ( !function_exists( 'colorcode_to_rgb' ) ):
function colorcode_to_rgb($colorcode){
  $colorcode = str_replace('#', '', $colorcode);
  $a['red'] = hexdec(substr($colorcode, 0, 2));
  $a['green'] = hexdec(substr($colorcode, 2, 2));
  $a['blue'] = hexdec(substr($colorcode, 4, 2));
  return $a;
}
endif;

//カラーコードをRGBのCSSコードに変換
if ( !function_exists( 'colorcode_to_rgb_css_code' ) ):
function colorcode_to_rgb_css_code($colorcode, $opacity = 0.2){
  $a = colorcode_to_rgb($colorcode);
  return 'rgba('.$a['red'].', '.$a['green'].', '.$a['blue'].', '.$opacity.')';
}
endif;

