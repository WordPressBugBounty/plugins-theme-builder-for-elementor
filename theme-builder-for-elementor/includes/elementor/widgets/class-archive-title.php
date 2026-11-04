<?php
/**
 * Title and description of the current archive (category, tag, author, date, search, blog).
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use TBFE\Elementor\Context;

defined( 'ABSPATH' ) || exit;

class Archive_Title extends Base {

	public function get_name() {
		return 'tbfe-archive-title';
	}

	public function get_title() {
		return esc_html__( 'Archive Title', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-archive-title';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_title', array( 'label' => esc_html__( 'Archive title', 'theme-builder-for-elementor' ) ) );
		$this->add_tag_control( 'html_tag', 'h1' );
		$this->add_control(
			'prefix',
			array(
				'label'       => esc_html__( 'Show prefix', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'E.g. "Category:" before the name.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
			)
		);
		$this->add_control(
			'description',
			array(
				'label'   => esc_html__( 'Show description', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SWITCHER,
				'default' => 'yes',
			)
		);
		$this->add_control(
			'count',
			array(
				'label'       => esc_html__( 'Show the number of posts', 'theme-builder-for-elementor' ),
				'description' => esc_html__( 'E.g. "6 results" on the search results page.', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SWITCHER,
				'default'     => '',
			)
		);
		$this->add_control(
			'count_one',
			array(
				'label'     => esc_html__( 'Text for one post', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( '1 result', 'theme-builder-for-elementor' ),
				'condition' => array( 'count' => 'yes' ),
			)
		);
		$this->add_control(
			'count_many',
			array(
				'label'       => esc_html__( 'Text for more posts', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::TEXT,
				/* translators: %s: number of posts */
				'default'     => esc_html__( '%s results', 'theme-builder-for-elementor' ),
				/* translators: Help of the field above; keep %s, it stands for the value. */
				'description' => esc_html__( '%s is replaced with the number.', 'theme-builder-for-elementor' ),
				'condition'   => array( 'count' => 'yes' ),
			)
		);
		$this->add_control(
			'count_none',
			array(
				'label'     => esc_html__( 'Text when nothing is found', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => esc_html__( 'No results', 'theme-builder-for-elementor' ),
				'condition' => array( 'count' => 'yes' ),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Title', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_align_control( 'align', '{{WRAPPER}} .tbfe-archive-title', true );
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'selector' => '{{WRAPPER}} .tbfe-archive-title__title',
			)
		);
		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-archive-title__title' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'desc_typography',
				'label'     => esc_html__( 'Description typography', 'theme-builder-for-elementor' ),
				'selector'  => '{{WRAPPER}} .tbfe-archive-title__desc',
				'separator' => 'before',
			)
		);
		$this->add_control(
			'desc_color',
			array(
				'label'     => esc_html__( 'Description color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-archive-title__desc' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'desc_spacing',
			array(
				'label'      => esc_html__( 'Description spacing', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-archive-title__desc' => 'margin-top: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'      => 'count_typography',
				'label'     => esc_html__( 'Number typography', 'theme-builder-for-elementor' ),
				'selector'  => '{{WRAPPER}} .tbfe-archive-title__count',
				'separator' => 'before',
				'condition' => array( 'count' => 'yes' ),
			)
		);
		$this->add_control(
			'count_color',
			array(
				'label'     => esc_html__( 'Number color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-archive-title__count' => 'color: {{VALUE}};' ),
				'condition' => array( 'count' => 'yes' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * "6 results" for the current query (an example in the editor).
	 */
	private function count_text( array $settings ) {
		global $wp_query;
		$found = Context::is_template_preview() ? 6 : (int) $wp_query->found_posts;
		if ( 0 === $found ) {
			return (string) $settings['count_none'];
		}
		if ( 1 === $found ) {
			return (string) $settings['count_one'];
		}
		return str_replace( '%s', number_format_i18n( $found ), (string) $settings['count_many'] );
	}

	protected function render() {
		$settings          = $this->get_settings_for_display();
		list( $title, $d ) = Context::archive_texts( ! empty( $settings['prefix'] ) );
		$tag               = self::safe_tag( $settings['html_tag'], 'h1' );

		echo '<div class="tbfe-archive-title">';
		printf( '<%1$s class="tbfe-archive-title__title">%2$s</%1$s>', $tag, esc_html( $title ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- whitelisted tag.
		if ( ! empty( $settings['description'] ) && '' !== trim( (string) $d ) ) {
			echo '<div class="tbfe-archive-title__desc">' . wp_kses_post( $d ) . '</div>';
		}
		if ( ! empty( $settings['count'] ) ) {
			$count = $this->count_text( $settings );
			if ( '' !== $count ) {
				echo '<p class="tbfe-archive-title__count">' . esc_html( $count ) . '</p>';
			}
		}
		echo '</div>';
	}
}
