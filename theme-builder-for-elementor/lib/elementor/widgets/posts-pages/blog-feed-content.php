<?php

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use Elementor\Group_Control_Typography;

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
class TBFE_Pro_Blog_Feed_Content extends Widget_Base {

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
        return 'blog-content';
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
        return esc_html__('Content', 'tbfe');
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
        return 'eicon-post-content';
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
                'section_editor',
                [
                    'label' => esc_html__('Content', 'tbfe'),
                ]
        );

        $this->add_control(
                'content_type',
                [
                    'label' => esc_html__('Content', 'tbfe'),
                    'type' => Controls_Manager::SELECT,
                    'default' => 'excerpt',
                    'options' => [
                        'excerpt' => esc_html__('Excerpt', 'tbfe'),
                        'full' => esc_html__('Full Content', 'tbfe'),
                    ],
                ]
        );
        $this->add_control(
                'limit',
                [
                    'label' => esc_html__('Excerpt', 'tbfe'),
                    'type' => Controls_Manager::SLIDER,
                    'default' => [
                        'size' => 35,
                    ],
                    'range' => [
                        'ms' => [
                            'min' => 0,
                            'max' => 100,
                            'step' => 1,
                        ],
                    ],
                    'condition' => [
                        'content_type' => 'excerpt',
                    ],
                ]
        );


        $this->end_controls_section();

        $this->start_controls_section(
                'section_style',
                [
                    'label' => esc_html__('Content', 'tbfe'),
                    'tab' => Controls_Manager::TAB_STYLE,
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
                        'justify' => [
                            'title' => esc_html__('Justified', 'tbfe'),
                            'icon' => 'fa fa-align-justify',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .tbfe-pro-elementor-content' => 'text-align: {{VALUE}};',
                    ],
                ]
        );

        $this->add_control(
                'text_color',
                [
                    'label' => esc_html__('Text Color', 'tbfe'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}}' => 'color: {{VALUE}};',
                    ],
                ]
        );

        $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'typography',
                ]
        );

        $this->start_controls_tabs('footer_title_tabs_title');

        $this->start_controls_tab(
                'footer_title_tab_title_normal',
                [
                    'label' => esc_html__('Normal', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'footer_title_color',
                [
                    'label' => esc_html__('Links Color', 'tbfe'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} a' => 'color: {{VALUE}}',
                    ],
                ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
                'footer_title_tab_title_hover',
                [
                    'label' => esc_html__('Hover', 'tbfe'),
                ]
        );

        $this->add_responsive_control(
                'footer_title_color_hover',
                [
                    'label' => esc_html__('Links Color', 'tbfe'),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} a:hover' => 'color: {{VALUE}}',
                    ],
                ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

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
        $limit = $settings['limit']['size'];


        $this->add_render_attribute('tbfe-pro-content', 'class', ['tbfe-pro-elementor-content']);

        if (is_home() || is_archive() || is_singular(array('post', 'page'))) {
            ?>
            <div <?php echo $this->get_render_attribute_string('tbfe-pro-content'); ?>>
                <?php
                if ($settings['content_type'] == 'excerpt') {
                    echo wp_trim_words(wp_strip_all_tags(get_the_excerpt()), $limit);
                } else {
                    $content = apply_filters('the_content', get_post_field('post_content', get_the_ID()));
                    echo $content;
                }
                ?>
            </div>
            <?php
        } else {
            ?>
            <div <?php echo $this->get_render_attribute_string('tbfe-pro-content'); ?>>
                <?php
                $content = 'This is a dummy text to demonstration purposes. It will be replaced with the post content/excerpt. <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi scelerisque luctus velit. Etiam quis quam. Duis viverra diam non justo. Suspendisse sagittis ultrices augue. Duis sapien nunc, commodo et, interdum suscipit, sollicitudin et, dolor. Donec ipsum massa, ullamcorper in, auctor et. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Aenean placerat. Pellentesque sapien. Mauris metus. Maecenas libero. Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. In rutrum. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat. Praesent in mauris eu tortor porttitor accumsan. Nunc tincidunt ante vitae massa. Curabitur bibendum justo non orci. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Curabitur vitae diam non enim vestibulum interdum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Et harum quidem rerum facilis est et expedita distinctio. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus.</p><p>Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Aenean placerat. Pellentesque sapien. Mauris metus. Maecenas libero. Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. In rutrum. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat. Praesent in mauris eu tortor porttitor accumsan. Nunc tincidunt ante vitae massa. Curabitur bibendum justo non orci. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Curabitur vitae diam non enim vestibulum interdum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Et harum quidem rerum facilis est et expedita distinctio. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus.</p> End of the dummy content.';
                if ($settings['content_type'] == 'excerpt') {
                    echo wp_trim_words(wp_strip_all_tags($content), $limit);
                } else {
                    echo $content;
                }
                ?>
            </div>
            <?php
        }
    }

}
