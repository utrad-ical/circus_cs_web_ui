/**
 * Null feedback listener.
 */

var circus = circus || {};

circus.evalListener = (function() {
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
			return null; // nothing to do
		},
		validate: function (target)
		{
			return { register_ok: true}; // validation always succeeds
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