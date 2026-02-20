<?php
/**
 * Plugin Name: EQ Toolbox
 * Description: Ajustes simples (CTA, estilos) usando hooks y settings.
 * Version: 1.0.0
 * Author: María Emilia Quiroga
 */

if (!defined('ABSPATH')) exit;

final class EQ_Toolbox {
  const OPT_CTA = 'eq_toolbox_cta';
  const OPT_ENABLE = 'eq_toolbox_enable_cta';

  public static function init(): void {
    // Admin
    add_action('admin_init', [__CLASS__, 'register_settings']);

    // Front
    add_filter('the_content', [__CLASS__, 'filter_append_cta']);
    add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_styles']);
    
    //Shortcode
    add_shortcode('eq_cta', [__CLASS__, 'shortcode_cta']);
  }
  
  /** Devuelve el HTML del CTA (reutilizable) */
private static function render_cta_html(): string {
  $enabled = (bool) get_option(self::OPT_ENABLE, true);
  if (!$enabled) return '';

  $cta = trim((string) get_option(self::OPT_CTA, ''));
  if ($cta === '') return '';

  $block  = '<div class="eq-toolbox-cta">';
  $block .= wp_kses_post(wpautop($cta));
  $block .= '</div>';

  return $block;
}

/** SHORTCODE: [eq_cta] */
public static function shortcode_cta($atts = [], $content = null): string {
  // No mostrar en admin
  if (is_admin()) return '';

  // Permitimos forzar mostrar con: [eq_cta show="1"]
  $atts = shortcode_atts([
    'show' => '1',
  ], (array) $atts);

  if ($atts['show'] !== '1') return '';

  return self::render_cta_html();
}

  /** ACTION: registra opciones en Ajustes > Lectura */
  public static function register_settings(): void {
    register_setting('reading', self::OPT_ENABLE, [
      'type' => 'boolean',
      'sanitize_callback' => [__CLASS__, 'sanitize_bool'],
      'default' => true,
    ]);

    register_setting('reading', self::OPT_CTA, [
      'type' => 'string',
      'sanitize_callback' => 'wp_kses_post',
      'default' => '',
    ]);

    add_settings_field(
      self::OPT_ENABLE,
      'EQ Toolbox: activar CTA en posts',
      [__CLASS__, 'render_enable_field'],
      'reading',
      'default'
    );

    add_settings_field(
      self::OPT_CTA,
      'EQ Toolbox: texto del CTA',
      [__CLASS__, 'render_cta_field'],
      'reading',
      'default'
    );
  }

  public static function sanitize_bool($value): bool {
    return (bool) $value;
  }

  public static function render_enable_field(): void {
    $enabled = (bool) get_option(self::OPT_ENABLE, true);
    echo '<label style="display:flex;gap:8px;align-items:center;">';
    echo '<input type="checkbox" name="'.esc_attr(self::OPT_ENABLE).'" value="1" '.checked(true, $enabled, false).' />';
    echo '<span>Mostrar CTA al final del contenido en entradas individuales</span>';
    echo '</label>';
  }

  public static function render_cta_field(): void {
    $cta = (string) get_option(self::OPT_CTA, '');
    echo '<textarea name="'.esc_attr(self::OPT_CTA).'" rows="4" style="width:100%;max-width:640px;">'.esc_textarea($cta).'</textarea>';
    echo '<p class="description">Se mostrará al final del contenido (solo en single post).</p>';
  }

  /** FILTER: modifica el contenido del post */
 public static function filter_append_cta(string $content): string {
  if (is_admin() || !is_main_query() || !in_the_loop()) return $content;
  if (!is_singular('post')) return $content;

  // ✅ Si el post ya incluye el shortcode, no lo duplicamos
  if (has_shortcode($content, 'eq_cta')) {
    return $content;
  }

  $block = self::render_cta_html();
  if ($block === '') return $content;

  return $content . $block;
}

  /** ACTION: encola estilos solo cuando corresponde */
  public static function enqueue_styles(): void {
    if (!is_singular('post')) return;

    // CSS inline liviano (sin archivo aparte)
    $css = "
      .eq-toolbox-cta{
        margin-top:24px;
        padding:16px;
        border:1px solid #e6e6e6;
        border-radius:14px;
      }
      .eq-toolbox-cta p{ margin:0; }
    ";

    // wp-block-library suele existir; si no, usamos 'wp-block-library' igual
    wp_register_style('eq-toolbox-inline', false);
    wp_enqueue_style('eq-toolbox-inline');
    wp_add_inline_style('eq-toolbox-inline', $css);
  }
}

EQ_Toolbox::init();
