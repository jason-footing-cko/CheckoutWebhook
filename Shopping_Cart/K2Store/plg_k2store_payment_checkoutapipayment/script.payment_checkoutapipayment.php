<?php

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgK2StorePayment_checkoutapipaymentInstallerScript {

	function preflight( $type, $parent ) {

		$xmlfile = JPATH_ADMINISTRATOR.'/components/com_k2store/manifest.xml';
		$xml = JFactory::getXML($xmlfile);
		$version=(string)$xml->version;

		//check for minimum requirement
		// abort if the current K2Store release is older
		if( version_compare( $version, '2.0.2', 'lt' ) ) {
			Jerror::raiseWarning(null, 'You are using an old version of K2Store. Please upgrade to the latest version');
			return false;
		}

	}

}