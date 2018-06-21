<?php //ヘッダーエリア
/**
 * Cocoon WordPress Theme
 * @author: yhira
 * @link: https://wp-cocoon.com/
 * @license: http://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 */ ?>
<div id="header-container" class="header-container">
  <div class="header-container-in<?php echo get_additional_header_container_classes(); ?>">
    <header id="header" class="header<?php echo get_additional_header_classes(); ?> cf" itemscope itemtype="http://schema.org/WPHeader">

      <div id="header-in" class="header-in wrap cf" itemscope itemtype="http://schema.org/WebSite">

        <?php //キャッチフレーズがヘッダー上部のとき
        if (is_tagline_position_header_top()) {
           get_template_part('tmp/header-tagline');
        } ?>

        <?php //ロゴタグの生成
        generate_the_site_logo_tag(); ?>

        <?php //キャッチフレーズがヘッダー下部のとき
        if (is_tagline_position_header_bottom()) {
           get_template_part('tmp/header-tagline');
        } ?>

      </div>

    </header>

    <?php get_template_part('tmp/navi'); ?>
  </div><!-- /.header-container-in -->
</div><!-- /.header-container -->