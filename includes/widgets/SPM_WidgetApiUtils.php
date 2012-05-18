<?php
/**
 * This file contains a static class for widget api utilities
 *
 * @author dch
 */

global $wgSPMIP;
require_once $wgSPMIP . '/includes/widgets/models/SPM_OM_Widget.php';
require_once $wgSPMIP . '/includes/widgets/models/SPM_OM_WidgetField.php';

class SPMWidgetApiUtils {

	static function getOutputWOMObjects( $title, &$wom ) {
		foreach ( SPMWidgetUtils::getWidgetData( $title ) as $widget_name => $fields ) {
			$widget_model = new SPMWidgetModel( Title::newFromText( $widget_name )->getText() );
			$wom->appendChildObject( $widget_model );
			$wid = $widget_model->getObjectID();

			foreach ( $fields as $name => $params ) {
				$prop_settings = SPMWidgetUtils::getPropertySettings( $params['property'] );
				$fld_model = new SPMWidgetFieldModel(
					$params['label'],
					$name,
					$params['property'],
					$prop_settings['prop_instance']->getTypeID(),
					$prop_settings['prop_instance']->getAllPossibleValues( $prop_settings['title'] ),
					$params['params'] );
				$wom->appendChildObject( $fld_model, $wid );
			}
		}

		return true;
	}
}
