<?php

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

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
class TBFE_Pro_Blog_Feed_Categories extends Widget_Base {

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
        return 'blog-categories';
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
        return esc_html__('Post categories', 'tbfe');
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
        return 'eicon-folder-o';
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
        $this->start_controls_section(
                'section_categories', [
            'label' => esc_html__('Content', 'tbfe'),
                ]
        );

        $this->add_control(
                'separator', [
            'label' => esc_html__('Separator', 'tbfe'),
            'type' => Controls_Manager::TEXT,
            'default' => ' / ',
                ]
        );
        $this->add_control(
                'text', [
            'label' => esc_html__('Text before', 'tbfe'),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html__('Categories:', 'tbfe'),
            'placeholder' => esc_html__('Categories:', 'tbfe'),
                ]
        );
        $this->add_control(
                'icon', [
            'label' => esc_html__('Icon', 'tbfe'),
            'type' => Controls_Manager::ICONS,
            'label_block' => true,
            'default' => [
                'value' => 'fas fa-folder',
                'library' => 'fa-solid',
            ],
            'separator' => 'before',
                ]
        );

        $this->add_control(
                'icon_align', [
            'label' => esc_html__('Icon Position', 'tbfe'),
            'type' => Controls_Manager::SELECT,
            'default' => 'left',
            'options' => [
                'left' => esc_html__('Before', 'tbfe'),
                'right' => esc_html__('After', 'tbfe'),
            ],
            'condition' => [
                'icon!' => '',
            ],
                ]
        );

        $this->add_control(
                'icon_indent', [
            'label' => esc_html__('Icon Spacing', 'tbfe'),
            'type' => Controls_Manager::SLIDER,
            'range' => [
                'px' => [
                    'max' => 50,
                ],
            ],
            'condition' => [
                'icon!' => '',
            ],
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-categories .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .tbfe-pro-elementor-categories .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
            ],
                ]
        );


        $this->end_controls_section();

        $this->start_controls_section(
                'section_style', [
            'label' => esc_html__('Content', 'tbfe'),
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
                '{{WRAPPER}} .tbfe-pro-elementor-categories' => 'text-align: {{VALUE}};',
            ],
                ]
        );

        $this->end_controls_section();



        $this->start_controls_section(
                'text_style', [
            'label' => esc_html__('Text', 'tbfe'),
            'tab' => Controls_Manager::TAB_STYLE,
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(), [
            'name' => 'text_typography',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-feed-title',
                ]
        );


        $this->add_control(
                'text_color', [
            'label' => esc_html__('Text Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-feed-title' => 'color: {{VALUE}}',
            ],
                ]
        );
        $this->add_group_control(
                Group_Control_Border::get_type(), [
            'name' => 'text_border',
            'placeholder' => '1px',
            'default' => '1px',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-feed-title',
            'separator' => 'before',
                ]
        );

        $this->add_control(
                'text_border_radius', [
            'label' => esc_html__('Border Radius', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-feed-title' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
                ]
        );

        $this->add_group_control(
                Group_Control_Box_Shadow::get_type(), [
            'name' => 'text_box_shadow',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-feed-title',
                ]
        );

        $this->add_responsive_control(
                'text_text_padding', [
            'label' => esc_html__('Padding', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-feed-title' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                '{{WRAPPER}} .tbfe-pro-elementor-feed-title' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
            'separator' => 'before',
                ]
        );
        $this->end_controls_section();

        $this->start_controls_section(
                'cats_style', [
            'label' => esc_html__('Categories', 'tbfe'),
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
            'label' => esc_html__('Category Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
                'title_tab_title_hover', [
            'label' => esc_html__('Hover', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'title_color_hover', [
            'label' => esc_html__('Category Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a:hover' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_control(
                'separator_color', [
            'label' => esc_html__('Separator Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'separator' => 'before',
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-categories' => 'color: {{VALUE}}',
            ],
                ]
        );
        $this->add_group_control(
                Group_Control_Border::get_type(), [
            'name' => 'border',
            'placeholder' => '1px',
            'default' => '1px',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-categories a',
            'separator' => 'before',
                ]
        );

        $this->add_control(
                'border_radius', [
            'label' => esc_html__('Border Radius', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', '%'],
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-categories a' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
                ]
        );

        $this->add_group_control(
                Group_Control_Box_Shadow::get_type(), [
            'name' => 'button_box_shadow',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-categories a',
                ]
        );

        $this->add_responsive_control(
                'text_padding', [
            'label' => esc_html__('Padding', 'tbfe'),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => ['px', 'em', '%'],
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-categories a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                '{{WRAPPER}} .tbfe-pro-elementor-categories a' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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

        $categories_list = get_the_category_list($separator);

        $this->add_render_attribute([
            'tbfe-pro-categories' => [
                'class' => 'tbfe-pro-elementor-categories',
            ],
            'tbfe-pro-categories-title' => [
                'class' => 'tbfe-pro-elementor-feed-title',
            ],
            'icon-align' => [
                'class' => [
                    'tbfe-pro-elementor-icon elementor-button-icon',
                    'tbfe-pro-elementor-icon elementor-align-icon-' . $settings['icon_align'],
                ],
            ],
            'text' => [
                'class' => 'tbfe-pro-elementor-icon elementor-button-text',
            ],
        ]);

        if (is_home() || is_archive() || is_singular(array('post', 'page'))) {
            ?>
            <div <?php echo $this->get_render_attribute_string('tbfe-pro-categories'); ?>>
                <div <?php echo $this->get_render_attribute_string('tbfe-pro-categories-title'); ?>>
                    <?php if (!empty($settings['icon'])) : ?>
                        <span <?php echo $this->get_render_attribute_string('icon-align'); ?>>
                            <?php Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] ); ?>
                        </span>
                    <?php endif; ?>
                    <span <?php echo $this->get_render_attribute_string('text'); ?>><?php echo $settings['text']; ?></span>
                    <span><?php echo wp_kses_data($categories_list); ?></span>
                </div>
                
            </div>
            <?php
        } else {
            ?>
            <div <?php echo $this->get_render_attribute_string('tbfe-pro-categories'); ?>>
                <div <?php echo $this->get_render_attribute_string('tbfe-pro-categories-title'); ?>>
                    <?php if (!empty($settings['icon'])) : ?>
                        <span <?php echo $this->get_render_attribute_string('icon-align'); ?>>
                            <?php Icons_Manager::render_icon( $settings['icon'], [ 'aria-hidden' => 'true' ] ); ?>
                        </span>
                    <?php endif; ?>
                    <span <?php echo $this->get_render_attribute_string('text'); ?>><?php echo $settings['text']; ?></span>
                    <span><a href="#">Dummy category</a><?php echo esc_html($separator); ?><a href="#">Second category</a></span>
                </div>
            </div>
            <?php
        }
    }

}
