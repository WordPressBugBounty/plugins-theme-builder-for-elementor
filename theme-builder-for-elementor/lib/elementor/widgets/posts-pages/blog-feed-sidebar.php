<?php

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Elementor text editor widget.
 *
 * Elementor widget that displays a WYSIWYG text editor, just like the WordPress
 * editor.
 *
 * @since 1.0.0
 */
class TBFE_Pro_Blog_Feed_Sidebar extends Widget_Base {

    /**
     * Get widget name.
     *
     * Retrieve text editor widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'blog-sidebar';
    }

    /**
     * Get widget title.
     *
     * Retrieve text editor widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return esc_html__('Sidebar & Widgets', 'tbfe');
    }

    /**
     * Get widget icon.
     *
     * Retrieve text editor widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'eicon-sidebar';
    }

    /**
     * Get widget categories.
     *
     * Retrieve the list of categories the text editor widget belongs to.
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
     * Register text editor widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {
        global $wp_registered_sidebars;

        $options = [];

        if (!$wp_registered_sidebars) {
            $options[''] = esc_html__('No sidebars were found', 'tbfe');
        } else {
            $options[''] = esc_html__('Choose Sidebar', 'tbfe');

            foreach ($wp_registered_sidebars as $sidebar_id => $sidebar) {
                $options[$sidebar_id] = $sidebar['name'];
            }
        }

        $default_key = array_keys($options);
        $default_key = array_shift($default_key);

        $this->start_controls_section(
                'section_sidebar',
                [
                    'label' => esc_html__('Sidebar', 'tbfe'),
                ]
        );

        $this->add_control('sidebar', [
            'label' => esc_html__('Choose Sidebar', 'tbfe'),
            'type' => Controls_Manager::SELECT,
            //'default' => $default_key,
            'options' => $options,
        ]);

        $this->end_controls_section();



        $this->start_controls_section(
                'text_style', [
            'label' => esc_html__('Title', 'tbfe'),
            'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_responsive_control(
                'align', [
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
                '{{WRAPPER}} .widgettitle' => 'text-align: {{VALUE}};',
            ],
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(), [
            'name' => 'text_typography',
            'selector' => '{{WRAPPER}} .widgettitle h3',
                ]
        );


        $this->add_control(
                'text_color', [
            'label' => esc_html__('Text Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .widgettitle' => 'color: {{VALUE}}',
            ],
                ]
        );
        $this->add_control(
                'text_bg_color', [
            'label' => esc_html__('Background Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .widgettitle' => 'background-color: {{VALUE}}',
            ],
                ]
        );
        $this->add_group_control(
                Group_Control_Border::get_type(), [
            'name' => 'text_border',
            'placeholder' => '1px',
            'default' => '1px',
            'selector' => '{{WRAPPER}} .widgettitle',
            'separator' => 'before',
                ]
        );

        $this->add_control(
                'text_border_radius', [
            'label' => esc_html__('Border Radius', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .widgettitle' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
                ]
        );

        $this->add_group_control(
                Group_Control_Box_Shadow::get_type(), [
            'name' => 'text_box_shadow',
            'selector' => '{{WRAPPER}} .widgettitle',
                ]
        );

        $this->add_responsive_control(
                'text_text_padding', [
            'label' => esc_html__('Padding', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .widgettitle' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'before',
                ]
        );

        $this->add_responsive_control(
                'text_text_margin', [
            'label' => esc_html__('Margin', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .widgettitle' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'before',
                ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
                'cats_style', [
            'label' => esc_html__('Content', 'tbfe'),
            'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(), [
            'name' => 'typography',
            'selector' => '{{WRAPPER}} a',
                ]
        );

        $this->start_controls_tabs('footer_title_tabs_title');

        $this->start_controls_tab(
                'title_tab_title_normal', [
            'label' => esc_html__('Normal', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'title_color', [
            'label' => esc_html__('Text Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .widget' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
                'title_tab_title_links', [
            'label' => esc_html__('Links', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'title_color_links', [
            'label' => esc_html__('Text Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .widget a' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
                'title_tab_title_hover', [
            'label' => esc_html__('Links Hover', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'title_color_hover', [
            'label' => esc_html__('Text Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .widget a:hover' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        
        $this->add_control(
                'widget_bg_color', [
            'label' => esc_html__('Background Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'separator' => 'before',
            'selectors' => [
                '{{WRAPPER}} .widget' => 'background-color: {{VALUE}}',
            ],
                ]
        );
        $this->add_group_control(
                Group_Control_Border::get_type(), [
            'name' => 'border',
            'placeholder' => '1px',
            'default' => '1px',
            'selector' => '{{WRAPPER}} .widget',
            'separator' => 'before',
                ]
        );
        
        $this->add_control(
                'separator_color', [
            'label' => esc_html__('Separator Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'separator' => 'before',
            'selectors' => [
                '{{WRAPPER}} .widget ul li' => 'border-bottom: {{VALUE}}',
            ],
                ]
        );
        $this->add_group_control(
                Group_Control_Border::get_type(), [
            'name' => 'border',
            'placeholder' => '1px',
            'default' => '1px',
            'selector' => '{{WRAPPER}} .widget',
            'separator' => 'before',
                ]
        );

        $this->add_control(
                'border_radius', [
            'label' => esc_html__('Border Radius', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .widget' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
                ]
        );

        $this->add_group_control(
                Group_Control_Box_Shadow::get_type(), [
            'name' => 'button_box_shadow',
            'selector' => '{{WRAPPER}} .widget',
                ]
        );

        $this->add_responsive_control(
                'text_padding', [
            'label' => esc_html__('Padding', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .widget' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'before',
                ]
        );

        $this->add_responsive_control(
                'text_margin', [
            'label' => esc_html__('Margin', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .widget' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'before',
                ]
        );
        $this->end_controls_section();

        $this->end_controls_section();
    }

    /**
     * Render text editor widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {
        $settings = $this->get_settings();

        $separator = $settings['separator'];
        $sidebar = $this->get_settings_for_display('sidebar');


        $this->add_render_attribute([
            'wrapper' => [
                'class' => 'tbfe-pro-elementor-sidebar',
            ],
        ]);

        if (is_home() || is_archive() || is_singular(array('post', 'page'))) {
            ?>
            <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
                <?php dynamic_sidebar($sidebar); ?>
            </div>
            <?php
        } else {
            ?>
            <div <?php echo $this->get_render_attribute_string('wrapper'); ?>>
                <?php
                if (empty($sidebar)) {
                    the_widget('WP_Widget_Recent_Posts');
                    the_widget('WP_Widget_Categories');
                    the_widget('WP_Widget_Tag_Cloud');
                } else {
                    dynamic_sidebar($sidebar);
                }
                ?>

            </div>
            <?php
        }
    }

}
