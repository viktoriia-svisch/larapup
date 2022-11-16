var components = require('../components.js');
var peerDependentsMap = null;
function getPeerDependentsMap() {
	var peerDependentsMap = {};
	Object.keys(components.languages).forEach(function (language) {
		if (language === 'meta') {
			return false;
		}
		if (components.languages[language].peerDependencies) {
			var peerDependencies = components.languages[language].peerDependencies;
			if (!Array.isArray(peerDependencies)) {
				peerDependencies = [peerDependencies];
			}
			peerDependencies.forEach(function (peerDependency) {
				if (!peerDependentsMap[peerDependency]) {
					peerDependentsMap[peerDependency] = [];
				}
				peerDependentsMap[peerDependency].push(language);
			});
		}
	});
	return peerDependentsMap;
}
function getPeerDependents(mainLanguage) {
	if (!peerDependentsMap) {
		peerDependentsMap = getPeerDependentsMap();
	}
	return peerDependentsMap[mainLanguage] || [];
}
function loadLanguages(arr, withoutDependencies) {
	if (!arr) {
		arr = Object.keys(components.languages).filter(function (language) {
			return language !== 'meta';
		});
	}
	if (arr && !arr.length) {
		return;
	}
	if (!Array.isArray(arr)) {
		arr = [arr];
	}
	arr.forEach(function (language) {
		if (!components.languages[language]) {
			console.warn('Language does not exist ' + language);
			return;
		}
		if (!withoutDependencies && components.languages[language].require) {
			loadLanguages(components.languages[language].require);
		}
		var pathToLanguage = './prism-' + language;
		delete require.cache[require.resolve(pathToLanguage)];
		delete Prism.languages[language];
		require(pathToLanguage);
		var dependents = getPeerDependents(language).filter(function (dependent) {
			if (Prism.languages[dependent]) {
				delete Prism.languages[dependent];
				return true;
			}
			return false;
		});
		if (dependents.length) {
			loadLanguages(dependents, true);
		}
	});
}
module.exports = function (arr) {
	loadLanguages(arr);
};
