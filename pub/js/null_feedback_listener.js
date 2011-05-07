/**
 * Null feedback listener.
 */
var evalListener = (function() {
	var global = {
		setup: function () {
			// nothing to do
		},
		set: function (target, value)
		{
			// nothing to do
		},
		get: function (target)
		{
			// nothing to do
		},
		validate: function (target)
		{
			return true; // validation always succeeds
		},
		disable: function (target)
		{
			// nothing to do
		},
		enable: function (target)
		{
			// nothing to do
		}
	};
	return global;
})();