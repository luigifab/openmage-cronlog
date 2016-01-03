/**
 * Copyright 2012-2016 | Fabrice Creuzot (luigifab) <code~luigifab~info>
 * Created V/27/03/2015, updated D/05/04/2015, version 1
 * https://redmine.luigifab.info/projects/magento/wiki/cronlog
 *
 * This program is free software, you can redistribute it or modify
 * it under the terms of the GNU General Public License (GPL).
 */

// dépend de Prototype
var cronlog = {

	// mise à jour
	action: function (href) {
		new Ajax.Updater($('cronlog_grid_rw').up(), href, {
			onComplete: function () {
				// odd/even dans l'ordre inverse au chargement de la page
				decorateGeneric($$('#cronlog_grid_rw tbody tr'), ['even', 'odd']);
			}
		});
		return false;
	}
};

//if (typeof window.addEventListener === 'function')
//	window.addEventListener('load', cronlog.start, false);