<?php //AMPページでは呼び出さない（通常ページのみで呼び出す）
/**
 * Cocoon WordPress Theme
 * @author: yhira
 * @link: https://wp-cocoon.com/
 * @license: http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */
if ( !defined( 'ABSPATH' ) ) exit;

if (!is_amp()): ?>
  <?php //AdSense非同期スクリプトを出力
  global $_IS_ADSENSE_EXIST;
  //if ($_IS_ADSENSE_EXIST && !is_customize_preview() && !is_cocoon_settings_preview()) {
  if ($_IS_ADSENSE_EXIST && !is_customize_preview()) {
    echo ADSENSE_SCRIPT_CODE;
  }
  ?>
  <?php //Pinterestシェア用のスクリプト
  if (is_singular() && is_pinterest_share_pin_visible()): ?>
  <script async defer data-pin-height="28" data-pin-hover="true" src="//assets.pinterest.com/js/pinit.js"></script>
  <?php endif ?>
  <?php //Pinterestシェアボタン用のスクリプト
  if (is_singular() && (is_top_pinterest_share_button_visible() || is_bottom_pinterest_share_button_visible())): ?>
  <script>!function(d,i){if(!d.getElementById(i)){var j=d.createElement("script");j.id=i;j.src="//assets.pinterest.com/js/pinit_main.js";var w=d.getElementById(i);d.body.appendChild(j);}}(document,"pinterest-btn-js");</script>
  <?php endif ?>
  <?php //コピーシェアボタン用のスクリプト
  global $_MOBILE_COPY_BUTTON;
  if (is_top_copy_share_button_visible() || is_bottom_copy_share_button_visible() || $_MOBILE_COPY_BUTTON): ?>
  <div class="copy-info"><?php _e('タイトルとURLをコピーしました', THEME_NAME); ?></div>
  <script src="//cdn.jsdelivr.net/clipboard.js/1.5.13/clipboard.min.js"></script>
  <script>
  (function($){
    var clipboard = new Clipboard('.copy-button');//clipboardで使う要素を指定
    clipboard.on('success', function(e) {
      $('.copy-info').fadeIn(500).delay(1000).fadeOut(500);

      e.clearSelection();
    });
  })(jQuery);
  </script>
  <?php endif ?>
  <?php //カルーセルが表示されている時
  if (false && is_carousel_visible() && get_carousel_category_ids()): ?>
  <script>
  (function($){
    //カルーセルの表示
    $('.carousel').fadeIn();
  });
  </script>
  <?php endif ?>
  <?php //本文中のJavaScriptをまとめて出力
  global $_THE_CONTENT_SCRIPTS;
  if ($_THE_CONTENT_SCRIPTS): ?>
  <script><?php echo $_THE_CONTENT_SCRIPTS; ?></script>
  <?php endif ?>
<?php endif ?>
<?php //固定ヘッダー
if (is_header_fixed()): ?>
<script>
(function($){
  function stickyHeader(){
    <?php if (get_header_layout_type_center_logo()): ?>
    //トップメニュータイプに変更する
    $("#header-container-in").removeClass().addClass("header-container-in hlt-top-menu wrap");
    <?php endif; ?>
    $("#header-container").addClass("fixed-header");
    $("#header-container").css({
      'position': 'sticky',
      'top': '-100px',
    });
    $("#header-container").animate({'top': '0'}, 500);
  }
  function staticHeader(){
    <?php if (get_header_layout_type_center_logo()): ?>
    //センターロゴタイプに戻す
    $("#header-container-in").removeClass().addClass("header-container-in <?php echo get_additional_header_container_classes(); ?>");
    <?php endif; ?>
    $("#header-container").removeClass("fixed-header");
    $("#header-container").css({
      'position': 'static',
      'top': 'auto',
    });
  }
  var prevScrollTop = -1;
  var $window = $(window);
  var mobileWidth = 1023;
  $window.scroll(function(){
    var scrollTop = $window.scrollTop();
    var threashold = 600;
    var s1 = (prevScrollTop > threashold);
    var s2 = (scrollTop > threashold);
    var w = $window.width();

    // ヘッダーメニューの固定
    if (s1 ^ s2) {
      if (s2 && w >  mobileWidth) {
        stickyHeader();
      }
    }
    //console.log(w);
    if (scrollTop == 0 || w <=  mobileWidth) {
      staticHeader();
    }

    prevScrollTop = scrollTop;
  });

  //ウインドウがリサイズされたら発動
  $window.resize(function() {
    //ウインドウの幅を変数に格納
    var w = $window.width();
    if (w <=  mobileWidth) {//モバイル端末の場合
      staticHeader();
    } else {//パソコン端末の場合
      stickyHeader();
    }
  });
})($);
</script>
<?php endif; ?>

