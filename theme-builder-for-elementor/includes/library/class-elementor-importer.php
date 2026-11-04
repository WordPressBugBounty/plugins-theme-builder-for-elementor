<?php
/**
 * Access to Elementor's import of template content: new element IDs and the on_import step of every control
 * (it downloads the images into the media library).
 *
 * @package TBFE
 */

namespace TBFE\Library;

use Elementor\TemplateLibrary\Source_Local;

defined( 'ABSPATH' ) || exit;

class Elementor_Importer extends Source_Local {

	/**
	 * @param array $content Elementor elements.
	 * @return array
	 */
	public function prepare( array $content ) {
		$content = $this->replace_elements_ids( $content );
		return $this->process_export_import_content( $content, 'on_import' );
	}

	/**
	 * Content prepared for export (used by the demo library on the demos site).
	 *
	 * @param array $content Elementor elements.
	 * @return array
	 */
	public function export( array $content ) {
		return $this->process_export_import_content( $content, 'on_export' );
	}
}
