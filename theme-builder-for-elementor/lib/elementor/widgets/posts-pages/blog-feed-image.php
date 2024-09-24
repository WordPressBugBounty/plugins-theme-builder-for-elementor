<?php

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Css_Filter;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor image widget.
 *
 * Elementor widget that displays an image into the page.
 *
 * @since 1.0.0
 */
class TBFE_Pro_Blog_Feed_Image extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve image widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'tbfe-pro-blog-image';
    }

    /**
     * Get widget title.
     *
     * Retrieve image widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__('Featured Image', 'tbfe');
    }

    /**
     * Get widget icon.
     *
     * Retrieve image widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-image';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the image widget belongs to.
     *
     * Used to determine where to display the widget in the editor.
     *
     * @since 2.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return ['blog-layout'];
    }

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @since 2.1.0
     * @access public
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {
        return ['image', 'photo', 'visual'];
    }

    /**
     * Register image widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {
        $this->start_controls_section(
                'section_image',
                [
                    'label' => esc_html__('Featured Image', 'tbfe'),
                ]
        );

        $this->add_control(
                'image',
                [
                    'label' => esc_html__('Choose Dummy Image', 'tbfe'),
                    'type' => Controls_Manager::MEDIA,
                    'description' => esc_html__('Dummy image is for development purposes. It will be replaced with the correct post or page thumbnail.', 'tbfe'),
                    'dynamic' => [
                        'active' => true,
                    ],
                ]
        );
        $this->add_control(
                'default-image',
                [
                        'label' => __( 'Use as default image?', 'twp-pro' ),
                        'description' => esc_html__('Use this dummy image as default image, if there is no featured image defined.', 'tbfe'),
                        'type' => Controls_Manager::SWITCHER,
                        'default' => 'no',
                        'yes' => __( 'Yes', 'twp-pro' ),
                        'no' => __( 'No', 'twp-pro' ),
                        'separator' => 'after',
                ]
        );

        $this->add_control(
                'size',
                [
                    'label' => esc_html__('Image Size', 'tbfe'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'tbfe-pro-single',
                    'options' => self::get_image_sizess(),
                    'style_transfer' => true,
                ]
        );

        $this->add_responsive_control(
                'align',
                [
                    'label' => esc_html__('Alignment', 'tbfe'),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => esc_html__('Left', 'tbfe'),
                            'icon' => 'fa fa-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__('Center', 'tbfe'),
                            'icon' => 'fa fa-align-center',
                        ],
                        'right' => [
                            'title' => esc_html__('Right', 'tbfe'),
                            'icon' => 'fa fa-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                    ],
                ]
        );




        $this->end_controls_section();

        $this->start_controls_section(
                'section_style_image',
                [
                    'label' => esc_html__('Image', 'tbfe'),
                    'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_responsive_control(
                'width',
                [
                    'label' => esc_html__('Width', 'tbfe'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'unit' => '%',
                    ],
                    'tablet_default' => [
                        'unit' => '%',
                    ],
                    'mobile_default' => [
                        'unit' => '%',
                    ],
                    'size_units' => ['%', 'px', 'vw'],
                    'range' => [
                        '%' => [
                            'min' => 1,
                            'max' => 100,
                        ],
                        'px' => [
                            'min' => 1,
                            'max' => 1000,
                        ],
                        'vw' => [
                            'min' => 1,
                            'max' => 100,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-image img' => 'width: {{SIZE}}{{UNIT}};',
                    ],
                ]
        );

        $this->add_responsive_control(
                'space',
                [
                    'label' => esc_html__('Max Width', 'tbfe') . ' (%)',
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'unit' => '%',
                    ],
                    'tablet_default' => [
                        'unit' => '%',
                    ],
                    'mobile_default' => [
                        'unit' => '%',
                    ],
                    'size_units' => ['%'],
                    'range' => [
                        '%' => [
                            'min' => 1,
                            'max' => 100,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-image img' => 'max-width: {{SIZE}}{{UNIT}};',
                    ],
                ]
        );

        $this->add_control(
                'separator_panel_style',
                [
                    'type' => Controls_Manager::DIVIDER,
                    'style' => 'thick',
                ]
        );

        $this->start_controls_tabs('image_effects');

        $this->start_controls_tab('normal',
                [
                    'label' => esc_html__('Normal', 'tbfe'),
                ]
        );

        $this->add_control(
                'opacity',
                [
                    'label' => esc_html__('Opacity', 'tbfe'),
                    'type' => Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'max' => 1,
                            'min' => 0.10,
                            'step' => 0.01,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-image img' => 'opacity: {{SIZE}};',
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                [
                    'name' => 'css_filters',
                    'selector' => '{{WRAPPER}} .elementor-image img',
                ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab('hover',
                [
                    'label' => esc_html__('Hover', 'tbfe'),
                ]
        );

        $this->add_control(
                'opacity_hover',
                [
                    'label' => esc_html__('Opacity', 'tbfe'),
                    'type' => Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'max' => 1,
                            'min' => 0.10,
                            'step' => 0.01,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-image:hover img' => 'opacity: {{SIZE}};',
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                [
                    'name' => 'css_filters_hover',
                    'selector' => '{{WRAPPER}} .elementor-image:hover img',
                ]
        );

        $this->add_control(
                'background_hover_transition',
                [
                    'label' => esc_html__('Transition Duration', 'tbfe'),
                    'type' => Controls_Manager::SLIDER,
                    'range' => [
                        'px' => [
                            'max' => 3,
                            'step' => 0.1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-image img' => 'transition-duration: {{SIZE}}s',
                    ],
                ]
        );

        $this->add_control(
                'hover_animation',
                [
                    'label' => esc_html__('Hover Animation', 'tbfe'),
                    'type' => Controls_Manager::HOVER_ANIMATION,
                ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name' => 'image_border',
                    'selector' => '{{WRAPPER}} .elementor-image img',
                    'separator' => 'before',
                ]
        );

        $this->add_responsive_control(
                'image_border_radius',
                [
                    'label' => esc_html__('Border Radius', 'tbfe'),
                    'type' => Controls_Manager::DIMENSIONS,
                    'size_units' => ['px', '%'],
                    'selectors' => [
                        '{{WRAPPER}} .elementor-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                [
                    'name' => 'image_box_shadow',
                    'exclude' => [
                        'box_shadow_position',
                    ],
                    'selector' => '{{WRAPPER}} .elementor-image img',
                ]
        );

        $this->end_controls_section();
    }

    /**
     * Get size information for all currently-registered image sizes.
     *
     * @global $_wp_additional_image_sizes
     * @uses   get_intermediate_image_sizes()
     * @return array $sizes Data for all currently-registered image sizes.
     */
    private function get_image_sizess() {
        global $_wp_additional_image_sizes;

        $sizes = array();

        foreach (get_intermediate_image_sizes() as $_size) {
            if (in_array($_size, array('thumbnail', 'medium', 'medium_large', 'large'))) {
                $sizes[$_size]['width'] = get_option("{$_size}_size_w");
                $sizes[$_size]['height'] = get_option("{$_size}_size_h");
                $sizes[$_size]['crop'] = (bool) get_option("{$_size}_crop");
            } elseif (isset($_wp_additional_image_sizes[$_size])) {
                $sizes[$_size] = array(
                    'width' => $_wp_additional_image_sizes[$_size]['width'],
                    'height' => $_wp_additional_image_sizes[$_size]['height'],
                    'crop' => $_wp_additional_image_sizes[$_size]['crop'],
                );
            }
        }

        foreach ($sizes as $term => $value) {
            $items[$term] = $term . ' - ' . $value['width'] . ' x ' . $value['height'];
        }
        /** This filter is documented in wp-admin/includes/media.php */
        return $items;
    }

    /**
     * Get size information for a specific image size.
     *
     * @uses   get_image_sizes()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|array $size Size data about an image size or false if the size doesn't exist.
     */
    private function get_image_size($size) {
        $sizes = get_image_sizes();

        if (isset($sizes[$size])) {
            return $sizes[$size];
        }

        return false;
    }

    /**
     * Get the width of a specific image size.
     *
     * @uses   get_image_size()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|string $size Width of an image size or false if the size doesn't exist.
     */
    private function get_image_width($size) {
        if (!$size = get_image_size($size)) {
            return false;
        }

        if (isset($size['width'])) {
            return $size['width'];
        }

        return false;
    }

    /**
     * Get the height of a specific image size.
     *
     * @uses   get_image_size()
     * @param  string $size The image size for which to retrieve data.
     * @return bool|string $size Height of an image size or false if the size doesn't exist.
     */
    private function get_image_height($size) {
        if (!$size = get_image_size($size)) {
            return false;
        }

        if (isset($size['height'])) {
            return $size['height'];
        }

        return false;
    }

    /**
     * Render image widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings_for_display();


        $this->add_render_attribute('wrapper', 'class', 'elementor-image');

        if (!empty($settings['shape'])) {
            $this->add_render_attribute('wrapper', 'class', 'elementor-image-shape-' . $settings['shape']);
        }

        $this->add_render_attribute('wrapper', 'class', 'tbfe-pro-image-size-' . $settings['size']);

        if (is_home() || is_archive() || is_singular(array('post', 'page'))) {
            if ($settings['default-image'] == 'yes' && !has_post_thumbnail()) {
            ?>
                <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>

                    <?php echo wp_get_attachment_image($settings['image']['id'], $settings['size']); ?>

                </div>
                <?php    
                } elseif (has_post_thumbnail()) {
                ?>
                <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>

                    <?php the_post_thumbnail($settings['size']); ?>

                </div>

            <?php
            }
        } elseif ($settings['image'] != '') {
            ?>
            <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>

                <?php echo wp_get_attachment_image($settings['image']['id'], $settings['size']); ?>

            </div>
            <?php
        }
    }

}
