<?php
if (!defined('ABSPATH')) exit;


//******************************************************************************
//  カスタマイザー(メインビジュアル)
//******************************************************************************
if (!function_exists('hvn_header')):
function hvn_header($wp_customize) {

  // セクション
  $wp_customize->add_section(
    'hvn_header_section',
    array(
      'title'     => 'メインビジュアル',
      'panel'     => 'hvn_cocoon',
      'priority'  => 3,
    )
  );


  // コントロール
  $wp_customize->add_setting('hvn_label1_header_section');
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_label1_header_section',
      array(
        'label'       => '■ ヘッダーロゴ',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_label1_header_section',
        'type'        => 'hidden',
      )
    )
  );


  $wp_customize->add_setting('the_site_logo_url');
  $wp_customize->add_control(
    new WP_Customize_Image_Control(
      $wp_customize,
      'the_site_logo_url',
      array(
        'description' => '画像',
        'section'     => 'hvn_header_section',
        'settings'    => 'the_site_logo_url',
        'mime_type'   => 'image',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_logo_setting', array('default' => false));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_logo_setting',
      array(
        'label'   => 'メインビジュアル表示',
        'section' => 'hvn_header_section',
        'settings'=> 'hvn_header_logo_setting',
        'type'    => 'checkbox',
      )
    )
  );


  $wp_customize->add_setting('hvn_label2_header_section');
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_label2_header_section',
      array(
        'label'       => '■ メインビジュアル',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_label2_header_section',
        'type'        => 'hidden',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_setting', array('default' => 'none'));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_setting',
      array(
        'section'   => 'hvn_header_section',
        'settings'  => 'hvn_header_setting',
        'type'      => 'select',
        'choices'   => array(
          'none'    => 'なし',
          'video'   => '動画',
          'image'   => '画像',
        )
      )
    )
  );


  $wp_customize->add_setting('hvn_header_message_setting');
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_message_setting',
      array(
        'description' => 'タイトルテキスト',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_header_message_setting',
        'type'        => 'text',
      )
    )
  );


  $wp_customize->add_setting('hvn_appea_font_size_setting', array(
    'default' => 40,
    'sanitize_callback' => 'hvn_sanitize_number_range',
  ));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_appea_font_size_setting',
      array(
        'description' => '文字サイズ(10～50px)',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_appea_font_size_setting',
        'type'        => 'number',
        'input_attrs' => array(
          'min'       => 10,
          'max'       => 50,
          'required'  => '',
        )
      )
    )
  );


  $wp_customize->add_setting('hvn_header_vertival_setting', array('default' => false));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_vertival_setting',
      array(
        'label'   => 'テキスト縦書き',
        'section' => 'hvn_header_section',
        'settings'=> 'hvn_header_vertival_setting',
        'type'    => 'checkbox',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_scroll_setting', array('default' => '0'));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_scroll_setting',
      array(
        'description' => 'Scrollボタン',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_header_scroll_setting',
        'type'        => 'select',
        'choices'     => array(
          '0'         => 'なし',
          '1'         => '線',
          '2'         => '矢印',
        )
      )
    )
  );


  $wp_customize->add_setting('hvn_header_wave_setting', array('default' => false));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_wave_setting',
      array(
        'label'   => '波線',
        'section' => 'hvn_header_section',
        'settings'=> 'hvn_header_wave_setting',
        'type'    => 'checkbox',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_video_setting');
  $wp_customize->add_control(
    new WP_Customize_Media_Control(
      $wp_customize,
      'hvn_header_video_setting',
      array(
        'label'     => '動画',
        'section'   => 'hvn_header_section',
        'settings'  => 'hvn_header_video_setting',
        'mime_type' => 'video',
      )
    )
  );


  for ($i=1; $i<=3; $i++) {
    $label = null;
    if ($i == 1) {
      $label = '画像';
    }
    $wp_customize->add_setting('hvn_header_img' . $i . '_setting');
    $wp_customize->add_control(
      new WP_Customize_Media_Control(
        $wp_customize,
        'hvn_header_img' . $i . '_setting',
        array(
          'label'       => $label,
          'description' => '画像[' . $i . ']',
          'section'     => 'hvn_header_section',
          'settings'    => 'hvn_header_img'. $i . '_setting',
          'mime_type'   => 'image',
        )
      )
    );
  }


  $wp_customize->add_setting('hvn_label3_header_section');
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_label3_header_section',
      array(
        'label'       => '■ スライドオプション',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_label3_header_section',
        'type'        => 'hidden',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_fade_setting', array('default' => 'fade'));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_fade_setting',
      array(
        'description' => 'スライド切り替え',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_header_fade_setting',
        'type'        => 'radio',
        'choices'     => array(
          'fade'      => 'フェード',
          'horizontal'=> '横',
          'vertical'  => '縦',
          'h-split'   => '横分割',
          'v-split'   => '縦分割',
        ),
      )
    )
  );


  $wp_customize->add_setting('hvn_header_animation_setting', array('default' => '0'));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_animation_setting',
      array(
        'description' => 'スライド中のズーム',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_header_animation_setting',
        'type'        => 'radio',
        'choices'     => array(
          '0' => 'なし',
          '1' => 'イン',
          '2' => 'アウト',
        )
      )
    )
  );


  $wp_customize->add_setting('hvn_label4_header_section');
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_label4_header_section',
      array(
        'label'       => '■ フィルター',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_label4_header_section',
        'type'        => 'hidden',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_filter_setting', array('default' => '0'));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_filter_setting',
      array(
        'section'   => 'hvn_header_section',
        'settings'  => 'hvn_header_filter_setting',
        'type'      => 'select',
        'choices'   => array(
          '0' => 'なし',
          '1' => 'ドット黒',
          '2' => 'ドット白',
          '3' => '走査線横',
          '4' => 'モノクロ',
        )
      )
    )
  );


  $wp_customize->add_setting('hvn_label5_header_section');
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_label5_header_section',
      array(
        'label'       => '■ オーバーレイ',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_label5_header_section',
        'type'        => 'hidden',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_color_setting');
  $wp_customize->add_control(
    new WP_Customize_Color_Control(
      $wp_customize,
      'hvn_header_color_setting',
      array(
        'description' => 'メインビジュアルに被せるカラーレイヤー',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_header_color_setting',
      )
    )
  );


  $wp_customize->add_setting('hvn_header_opacity_setting', array(
    'default' => 0,
    'sanitize_callback' => 'hvn_sanitize_number_range',
  ));
  $wp_customize->add_control(
    new WP_Customize_Control(
      $wp_customize,
      'hvn_header_opacity_setting',
      array(
        'description' => '不透明度(opacity値0～50%)',
        'section'     => 'hvn_header_section',
        'settings'    => 'hvn_header_opacity_setting',
        'type'        => 'number',
        'input_attrs' => array(
          'step'      => 10,
          'min'       => 0,
          'max'       => 50,
          'required'  => '',
        )
      )
    )
  );
}
endif;
