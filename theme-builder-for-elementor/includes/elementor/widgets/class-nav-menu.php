<?php
/**
 * Navigation menu with drop-downs and a mobile toggle.
 *
 * @package TBFE
 */

namespace TBFE\Elementor\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined( 'ABSPATH' ) || exit;

class Nav_Menu extends Base {

	public function get_name() {
		return 'tbfe-nav-menu';
	}

	public function get_title() {
		return esc_html__( 'Nav Menu', 'theme-builder-for-elementor' );
	}

	public function get_icon() {
		return 'eicon-nav-menu';
	}

	public function get_keywords() {
		return array( 'menu', 'navigation', 'nav', 'header' );
	}

	public function get_script_depends() {
		return array( 'tbfe-nav-menu' );
	}

	protected function register_controls() {
		$menus = array( '' => esc_html__( '— Select —', 'theme-builder-for-elementor' ) );
		foreach ( wp_get_nav_menus() as $menu ) {
			$menus[ $menu->term_id ] = $menu->name;
		}

		$this->start_controls_section( 'section_menu', array( 'label' => esc_html__( 'Menu', 'theme-builder-for-elementor' ) ) );
		$this->add_control(
			'menu',
			array(
				'label'       => esc_html__( 'Menu', 'theme-builder-for-elementor' ),
				'type'        => Controls_Manager::SELECT,
				'default'     => '',
				'options'     => $menus,
				/* translators: %s: link to the menus screen */
				'description' => sprintf( esc_html__( 'Edit the menus in %s.', 'theme-builder-for-elementor' ), '<a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '" target="_blank">' . esc_html__( 'Appearance › Menus', 'theme-builder-for-elementor' ) . '</a>' ),
			)
		);
		$this->add_control(
			'layout',
			array(
				'label'        => esc_html__( 'Layout', 'theme-builder-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'horizontal',
				'options'      => array(
					'horizontal' => esc_html__( 'Horizontal', 'theme-builder-for-elementor' ),
					'vertical'   => esc_html__( 'Vertical', 'theme-builder-for-elementor' ),
				),
				'prefix_class' => 'tbfe-nav--',
			)
		);
		$this->add_responsive_control(
			'align',
			array(
				'label'     => esc_html__( 'Alignment', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start'    => array(
						'title' => esc_html__( 'Left', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'        => array(
						'title' => esc_html__( 'Center', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'      => array(
						'title' => esc_html__( 'Right', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
					'space-between' => array(
						'title' => esc_html__( 'Stretch', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-stretch',
					),
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-nav__menu' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'breakpoint',
			array(
				'label'        => esc_html__( 'Mobile menu from', 'theme-builder-for-elementor' ),
				'type'         => Controls_Manager::SELECT,
				'default'      => 'tablet',
				'options'      => array(
					'tablet' => esc_html__( 'Tablet', 'theme-builder-for-elementor' ),
					'mobile' => esc_html__( 'Mobile', 'theme-builder-for-elementor' ),
					'none'   => esc_html__( 'Never', 'theme-builder-for-elementor' ),
				),
				'prefix_class' => 'tbfe-nav-break-',
			)
		);
		$this->add_control(
			'toggle_label',
			array(
				'label'     => esc_html__( 'Toggle label', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::TEXT,
				'default'   => '',
				'condition' => array( 'breakpoint!' => 'none' ),
			)
		);
		$this->end_controls_section();

		// Style: items.
		$this->start_controls_section(
			'section_items',
			array(
				'label' => esc_html__( 'Menu items', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'item_typography',
				'selector' => '{{WRAPPER}} .tbfe-nav__menu > li > a',
			)
		);
		$this->start_controls_tabs( 'item_tabs' );
		foreach ( array(
			'normal' => array( esc_html__( 'Normal', 'theme-builder-for-elementor' ), '{{WRAPPER}} .tbfe-nav__menu > li > a', '{{WRAPPER}} .tbfe-nav__menu > li > .tbfe-nav__sub-toggle' ),
			'hover'  => array( esc_html__( 'Hover', 'theme-builder-for-elementor' ), '{{WRAPPER}} .tbfe-nav__menu > li > a:hover, {{WRAPPER}} .tbfe-nav__menu > li > a:focus', '{{WRAPPER}} .tbfe-nav__menu > li:hover > .tbfe-nav__sub-toggle, {{WRAPPER}} .tbfe-nav__menu > li > .tbfe-nav__sub-toggle:focus' ),
			'active' => array( esc_html__( 'Active', 'theme-builder-for-elementor' ), '{{WRAPPER}} .tbfe-nav__menu > li.current-menu-item > a, {{WRAPPER}} .tbfe-nav__menu > li.current-menu-ancestor > a', '{{WRAPPER}} .tbfe-nav__menu > li.current-menu-item > .tbfe-nav__sub-toggle, {{WRAPPER}} .tbfe-nav__menu > li.current-menu-ancestor > .tbfe-nav__sub-toggle' ),
		) as $state => $data ) {
			$this->start_controls_tab( 'item_' . $state, array( 'label' => $data[0] ) );
			$this->add_control(
				'item_color_' . $state,
				array(
					'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					// The arrow of a sub-menu has the color of its item.
					'selectors' => array( $data[1] . ', ' . $data[2] => 'color: {{VALUE}};' ),
				)
			);
			$this->add_control(
				'item_bg_' . $state,
				array(
					'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array( $data[1] => 'background-color: {{VALUE}};' ),
				)
			);
			$this->end_controls_tab();
		}
		$this->end_controls_tabs();
		$this->add_responsive_control(
			'item_padding',
			array(
				'label'      => esc_html__( 'Padding', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em' ),
				'separator'  => 'before',
				'selectors'  => array( '{{WRAPPER}} .tbfe-nav__menu > li > a' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};' ),
			)
		);
		$this->add_responsive_control(
			'item_gap',
			array(
				'label'      => esc_html__( 'Space between', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-nav__menu' => 'gap: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_control(
			'item_radius',
			array(
				'label'      => esc_html__( 'Border radius', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-nav__menu > li > a' => 'border-radius: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->end_controls_section();

		// Style: drop-down.
		$this->start_controls_section(
			'section_dropdown',
			array(
				'label' => esc_html__( 'Drop-down', 'theme-builder-for-elementor' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);
		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'sub_typography',
				'selector' => '{{WRAPPER}} .tbfe-nav .sub-menu a',
			)
		);
		$this->add_control(
			'sub_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-nav .sub-menu, {{WRAPPER}} .tbfe-nav.is-mobile .tbfe-nav__menu' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'sub_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-nav .sub-menu a' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'sub_color_hover',
			array(
				'label'     => esc_html__( 'Hover color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-nav .sub-menu a:hover, {{WRAPPER}} .tbfe-nav .sub-menu a:focus' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'sub_bg_hover',
			array(
				'label'     => esc_html__( 'Hover background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-nav .sub-menu a:hover, {{WRAPPER}} .tbfe-nav .sub-menu a:focus' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->add_responsive_control(
			'sub_width',
			array(
				'label'      => esc_html__( 'Width', 'theme-builder-for-elementor' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array( 'px' => array( 'min' => 120, 'max' => 500 ) ),
				'selectors'  => array( '{{WRAPPER}} .tbfe-nav .sub-menu' => 'min-width: {{SIZE}}{{UNIT}};' ),
			)
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'sub_shadow',
				'selector' => '{{WRAPPER}} .tbfe-nav .sub-menu',
			)
		);
		$this->end_controls_section();

		// Style: toggle.
		$this->start_controls_section(
			'section_toggle',
			array(
				'label'     => esc_html__( 'Mobile toggle', 'theme-builder-for-elementor' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array( 'breakpoint!' => 'none' ),
			)
		);
		$this->add_responsive_control(
			'toggle_align',
			array(
				'label'     => esc_html__( 'Alignment', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'flex-start' => array(
						'title' => esc_html__( 'Left', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-left',
					),
					'center'     => array(
						'title' => esc_html__( 'Center', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-center',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Right', 'theme-builder-for-elementor' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'selectors' => array( '{{WRAPPER}} .tbfe-nav__bar' => 'justify-content: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'toggle_color',
			array(
				'label'     => esc_html__( 'Color', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-nav__toggle' => 'color: {{VALUE}};' ),
			)
		);
		$this->add_control(
			'toggle_bg',
			array(
				'label'     => esc_html__( 'Background', 'theme-builder-for-elementor' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array( '{{WRAPPER}} .tbfe-nav__toggle' => 'background-color: {{VALUE}};' ),
			)
		);
		$this->end_controls_section();
	}

	/**
	 * Menu of the site when none is chosen or the chosen one does not exist (e.g. a demo imported from the library
	 * keeps the menu ID of the demos site): the menu of a theme location, else the first menu.
	 */
	private static function fallback_menu() {
		foreach ( (array) get_nav_menu_locations() as $menu_id ) {
			if ( $menu_id && is_nav_menu( $menu_id ) ) {
				return (int) $menu_id;
			}
		}
		$menus = wp_get_nav_menus();
		return $menus ? (int) $menus[0]->term_id : 0;
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$menu_id  = absint( $settings['menu'] );
		if ( ! $menu_id || ! is_nav_menu( $menu_id ) ) {
			$menu_id = self::fallback_menu();
		}
		if ( ! $menu_id ) {
			$this->editor_notice( __( 'Choose a menu in the widget settings.', 'theme-builder-for-elementor' ) );
			return;
		}
		$list_id = 'tbfe-nav-' . $this->get_id();
		$menu    = wp_nav_menu(
			array(
				'menu'        => $menu_id,
				'container'   => false,
				'menu_class'  => 'tbfe-nav__menu',
				'menu_id'     => $list_id,
				'fallback_cb' => false,
				'echo'        => false,
				'depth'       => 4,
			)
		);
		if ( ! $menu ) {
			return;
		}
		$label = '' !== $settings['toggle_label'] ? $settings['toggle_label'] : __( 'Menu', 'theme-builder-for-elementor' );
		?>
		<nav class="tbfe-nav" aria-label="<?php echo esc_attr( wp_get_nav_menu_object( $menu_id )->name ); ?>">
			<?php if ( 'none' !== $settings['breakpoint'] ) : ?>
				<div class="tbfe-nav__bar">
					<button class="tbfe-nav__toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $list_id ); ?>">
						<span class="tbfe-nav__burger" aria-hidden="true"><span></span><span></span><span></span></span>
						<span class="<?php echo '' !== $settings['toggle_label'] ? 'tbfe-nav__toggle-text' : 'screen-reader-text'; ?>"><?php echo esc_html( $label ); ?></span>
					</button>
				</div>
			<?php endif; ?>
			<?php echo $menu; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core menu markup (wp_nav_menu escapes the items); mega menu panels add Elementor content, so no kses here. ?>
		</nav>
		<?php
	}
}
