<?php

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;
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
class TBFE_Pro_Blog_Feed_Author extends Widget_Base {

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
        return 'blog-author';
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
        return esc_html__('Post author', 'tbfe');
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
        return 'eicon-person';
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
                'section_author', [
            'label' => esc_html__('Content', 'tbfe'),
                ]
        );
        $this->add_control(
                'text', [
            'label' => esc_html__('Text before', 'tbfe'),
            'type' => Controls_Manager::TEXT,
            'default' => esc_html__('Author:', 'tbfe'),
            'placeholder' => esc_html__('Author:', 'tbfe'),
                ]
        );
        $this->add_control(
                'text_indent', [
            'label' => esc_html__('Text Spacing', 'tbfe'),
            'type' => Controls_Manager::SLIDER,
            'range' => [
                'px' => [
                    'max' => 50,
                ],
            ],
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-feed-title' => 'margin-right: {{SIZE}}{{UNIT}};',
            ],
                ]
        );
        $this->add_control(
                'icon', [
            'label' => esc_html__('Icon', 'tbfe'),
            'type' => Controls_Manager::ICONS,
            'default' => [
                'value' => 'fas fa-user',
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
                '{{WRAPPER}} .tbfe-pro-elementor-author .elementor-align-icon-right' => 'margin-left: {{SIZE}}{{UNIT}};',
                '{{WRAPPER}} .tbfe-pro-elementor-author .elementor-align-icon-left' => 'margin-right: {{SIZE}}{{UNIT}};',
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
            'separator' => 'after',
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
                '{{WRAPPER}} .tbfe-pro-elementor-author' => 'text-align: {{VALUE}};',
            ],
                ]
        );
        $this->add_group_control(
                Group_Control_Typography::get_type(), [
            'name' => 'typography',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-author a',
                ]
        );
        $this->start_controls_tabs('title_tabs_title');

        $this->start_controls_tab(
                'title_tab_title_normal', [
            'label' => esc_html__('Normal', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'title_color', [
            'label' => esc_html__('Author Color', 'tbfe'),
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
            'label' => esc_html__('Author Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} a:hover' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->add_responsive_control(
                'color_before_text', [
            'label' => esc_html__('Text Color', 'tbfe'),
            'type' => Controls_Manager::COLOR,
            'separator' => 'before',
            'selectors' => [
                '{{WRAPPER}} .tbfe-pro-elementor-feed-title' => 'color: {{VALUE}}',
            ],
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(), [
            'name' => 'text_typography',
            'selector' => '{{WRAPPER}} .tbfe-pro-elementor-feed-title',
                ]
        );

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
        $this->add_render_attribute([
            'tbfe-pro-author' => [
                'class' => 'tbfe-pro-elementor-author',
            ],
            'tbfe-pro-author-title' => [
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
        ?>
        <div <?php echo $this->get_render_attribute_string('tbfe-pro-author'); ?>>
            <div <?php echo $this->get_render_attribute_string('tbfe-pro-author-title'); ?>>
                <?php if (!empty($settings['icon'])) : ?>
                    <span <?php echo $this->get_render_attribute_string('icon-align'); ?>>
                        <?php Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']); ?>
                    </span>
                <?php endif; ?>
                <span <?php echo $this->get_render_attribute_string('text'); ?>><?php echo $settings['text']; ?></span>
                <span>
                    <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'), get_the_author_meta('user_nicename'))); ?>">
                        <?php the_author(); ?>
                    </a> 
                </span>
            </div>
        </div>
        <?php
    }

}
