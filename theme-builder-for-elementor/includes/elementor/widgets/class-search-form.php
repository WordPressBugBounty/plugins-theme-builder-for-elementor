<?php
/**
 * Search form.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;

defined( 'ABSPATH' ) || exit;

class Search_Form extends Base {

	public function get_name() {
		return 'tbfe-search-form';
	}

	public function get_title() {
		return esc_html__( 'Search Form', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-search';
	}

	protected function register_controls() {
		$this->start_controls_section( 'section_search', array( 'label' => esc_html__( 'Search form', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'layout',
			array(
				'label'   => esc_html__( 'Layout', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'form',
				'options' => array(
					'form'     => esc_html__( 'Search field', 'theme-builder-for-elementor' ),
					'dropdown' => esc_html__( 'Icon, the field drops down', 'theme-builder-for-elementor' ),
					'overlay'  => esc_html__( 'Icon, the field over the whole screen', 'theme-builder-for-elementor' ),
				),
			)
		);
		$this->add_control(
			'toggle_icon',
			array(
				'label'     => esc_html__( 'Icon', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::ICONS,
				'default'   => array(
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				),
				'condition' => array( 'layout!' => 'form' ),
			)
		);
		$this->add_control(
			'toggle_color',
			array(
				'label'     => esc_html__( 'Icon color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-search-toggle__button' => 'color: {{VALUE}};' ),
				'condition' => array( 'layout!' => 'form' ),
			)
		);
		$this->add_responsive_control(
			'toggle_size',
			array(
				'label'      => esc_html__( 'Icon size', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 10, 'max' => 48 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-search-toggle__button' => 'font-size: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout!' => 'form' ),
			)
		);
		$this->add_responsive_control(
			'dropdown_width',
			array(
				'label'      => esc_html__( 'Field width', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 180, 'max' => 600 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-search-toggle--dropdown .tbfe-search-toggle__panel' => 'width: {{SIZE}}{{UNIT}};' ),
				'condition'  => array( 'layout' => 'dropdown' ),
			)
		);
		$this->add_control(
			'overlay_bg',
			array(
				'label'     => esc_html__( 'Screen background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-search-toggle--overlay .tbfe-search-toggle__panel' => 'background-color: {{VALUE}};' ),
				'condition' => array( 'layout' => 'overlay' ),
			)
		);
		$this->add_control(
			'placeholder',
			array(
				'label'   => esc_html__( 'Placeholder', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Search…', 'theme-builder-for-elementor' ),
			)
		);
		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'Button text', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::TEXT,
				'default' => '',
			)
		);
		$this->add_control(
			'button_icon',
			array(
				'label'   => esc_html__( 'Button icon', 'theme-builder-for-elementor' ),
				'type'    => Controls_Manager::ICONS,
				'default' => array(
					'value'   => 'fas fa-search',
					'library' => 'fa-solid',
				),
			)
		);
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			array(
				'label' => esc_html__( 'Field', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'typography',
				'selector' => '{{WRAPPER}} .tbfe-search__input, {{WRAPPER}} .tbfe-search__button',
			)
		);
		$this->add_responsive_control(
			'height',
			array(
				'label'      => esc_html__( 'Height', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 28, 'max' => 90 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-search' => '--tbfe-search-height: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'input_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-search__input' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'input_color',
			array(
				'label'     => esc_html__( 'Text color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-search__input' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'input_border',
				'selector' => '{{WRAPPER}} .tbfe-search__input',
			)
		);
		$this->add_control(
			'radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-search' => '--tbfe-search-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'button_bg',
			array(
				'label'     => esc_html__( 'Button background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'separator' => 'before',
				'selectors' => array( '{{WRAPPER}} .tbfe-search__button' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_color',
			array(
				'label'     => esc_html__( 'Button color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-search__button' => 'color: {{VALUE}}; fill: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'button_bg_hover',
			array(
				'label'     => esc_html__( 'Button hover background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-search__button:hover, {{WRAPPER}} .tbfe-search__button:focus' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$id       = 'tbfe-search-' . $this->get_id();

		$icon = '';
		if ( ! empty( $settings['button_icon']['value'] ) ) {
			ob_start();
			Icons_Manager::render_icon( $settings['button_icon'], array( 'aria-hidden' => 'true' ) );
			$icon = ob_get_clean();
		}
		$text   = '' !== $settings['button_text'] ? '<span>' . esc_html( $settings['button_text'] ) . '</span>' : '';
		$layout = isset( $settings['layout'] ) && in_array( $settings['layout'], array( 'dropdown', 'overlay' ), true ) ? $settings['layout'] : '';
		if ( $layout ) {
			$toggle = '';
			if ( ! empty( $settings['toggle_icon']['value'] ) ) {
				ob_start();
				Icons_Manager::render_icon( $settings['toggle_icon'], array( 'aria-hidden' => 'true' ) );
				$toggle = ob_get_clean();
			}
			printf(
				'<div class="tbfe-search-toggle tbfe-search-toggle--%1$s"><button type="button" class="tbfe-search-toggle__button" aria-expanded="false" aria-controls="%2$s-panel" aria-label="%3$s">%4$s</button><div class="tbfe-search-toggle__panel" id="%2$s-panel" hidden>',
				esc_attr( $layout ),
				esc_attr( $id ),
				esc_attr__( 'Open the search', 'theme-builder-for-elementor' ),
				\TBFE\Plugin::kses( $toggle ) // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses().
			);
			if ( 'overlay' === $layout ) {
				printf( '<button type="button" class="tbfe-search-toggle__close" aria-label="%s">&times;</button>', esc_attr__( 'Close the search', 'theme-builder-for-elementor' ) );
			}
		}
		?>
		<form class="tbfe-search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'Search for:', 'theme-builder-for-elementor' ); ?></label>
			<input class="tbfe-search__input" id="<?php echo esc_attr( $id ); ?>" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php echo esc_attr( $settings['placeholder'] ); ?>">
			<button class="tbfe-search__button" type="submit" aria-label="<?php esc_attr_e( 'Search', 'theme-builder-for-elementor' ); ?>"><?php echo \TBFE\Plugin::kses( $icon . $text ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped with Plugin::kses(). ?></button>
		</form>
		<?php
		if ( $layout ) {
			echo '</div></div>';
		}
	}

	public function get_style_depends(): array {
		return array( 'tbfe-widgets', 'tbfe-search-toggle' );
	}

	public function get_script_depends() {
		return array( 'tbfe-search-toggle' );
	}
}
