/*! Select2 4.1.0-rc.0 | https://github.com/select2/select2/blob/master/LICENSE.md */

var translation_vendors = JSON.parse(translation_vendor);	

! function() {
	if (jQuery && jQuery.fn && jQuery.fn.select2 && jQuery.fn.select2.amd) var e = jQuery.fn.select2.amd;
	e.define("select2/i18n/en_us", [], function() {
		return {
			errorLoading: function() {
				return translation_vendors.the_results_could_loaded;
			},
			inputTooLong: function(e) {
				var n = e.input.length - e.maximum,
					r = "Please delete " + n + " character";
				return 1 != n && (r += "s"), r
			},
			inputTooShort: function(e) {
				return "Please enter " + (e.minimum - e.input.length) + " or more characters"
			},
			loadingMore: function() {
				return translation_vendors.loading_more_results;
			},
			maximumSelected: function(e) {
				var n = "You can only select " + e.maximum + " item";
				return 1 != e.maximum && (n += "s"), n
			},
			noResults: function() {
				return translation_vendors.no_results;
			},
			searching: function() {
				return translation_vendors.searching;
			},
			removeAllItems: function() {
				return translation_vendors.remove_all_items;
			},
			removeItem: function() {
				return translation_vendors.remove_item;
			},
			search: function() {
				return translation_vendors.search;
			}
		}
	}), e.define, e.require
}();
