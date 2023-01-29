function attach() {
    var container = this.options.container;
    if (container instanceof HTMLElement) {
        var style = window.getComputedStyle(container);
        if (style.position === 'static') {
            container.style.position = 'relative';
        }
    }
    container.addEventListener('scroll', this._scroll);
    window.addEventListener('resize', this._scroll);
    this._scroll();
    this.attached = true;
}
function inViewport(el) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : { tolerance: 0 };
    if (!el) {
        throw new Error('You should specify the element you want to test');
    }
    if (typeof el === 'string') {
        el = document.querySelector(el);
    }
    var elRect = el.getBoundingClientRect();
    return (
        elRect.bottom - options.tolerance > 0 &&
        elRect.right - options.tolerance > 0 &&
        elRect.left + options.tolerance < (window.innerWidth || document.documentElement.clientWidth) &&
        elRect.top + options.tolerance < (window.innerHeight || document.documentElement.clientHeight)
    );
}
function inContainer(el) {
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : { tolerance: 0, container: '' };
    if (!el) {
        throw new Error('You should specify the element you want to test');
    }
    if (typeof el === 'string') {
        el = document.querySelector(el);
    }
    if (typeof options === 'string') {
        options = {
            tolerance: 0,
            container: document.querySelector(options)
        };
    }
    if (typeof options.container === 'string') {
        options.container = document.querySelector(options.container);
    }
    if (options instanceof HTMLElement) {
        options = {
            tolerance: 0,
            container: options
        };
    }
    if (!options.container) {
        throw new Error('You should specify a container element');
    }
    var containerRect = options.container.getBoundingClientRect();
    return (
        el.offsetTop + el.clientHeight - options.tolerance > options.container.scrollTop &&
        el.offsetLeft + el.clientWidth - options.tolerance > options.container.scrollLeft &&
        el.offsetLeft + options.tolerance < containerRect.width + options.container.scrollLeft &&
        el.offsetTop + options.tolerance < containerRect.height + options.container.scrollTop
    );
}
function eventHandler() {
    var trackedElements = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
    var options = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : { tolerance: 0 };
    var selectors = Object.keys(trackedElements);
    var testVisibility = void 0;
    if (!selectors.length) return;
    if (options.container === window) {
        testVisibility = inViewport;
    } else {
        testVisibility = inContainer;
    }
    selectors.forEach(function (selector) {
        trackedElements[selector].nodes.forEach(function (item) {
            if (testVisibility(item.node, options)) {
                item.wasVisible = item.isVisible;
                item.isVisible = true;
            } else {
                item.wasVisible = item.isVisible;
                item.isVisible = false;
            }
            if (item.isVisible === true && item.wasVisible === false) {
                if (!trackedElements[selector].enter) return;
                Object.keys(trackedElements[selector].enter).forEach(function (callback) {
                    if (typeof trackedElements[selector].enter[callback] === 'function') {
                        trackedElements[selector].enter[callback](item.node, 'enter');
                    }
                });
            }
            if (item.isVisible === false && item.wasVisible === true) {
                if (!trackedElements[selector].leave) return;
                Object.keys(trackedElements[selector].leave).forEach(function (callback) {
                    if (typeof trackedElements[selector].leave[callback] === 'function') {
                        trackedElements[selector].leave[callback](item.node, 'leave');
                    }
                });
            }
        });
    });
}
function debouncedScroll() {
    var _this = this;
    var timeout = void 0;
    return function () {
        clearTimeout(timeout);
        timeout = setTimeout(function () {
            eventHandler(_this.trackedElements, _this.options);
        }, _this.options.debounce);
    };
}
function destroy() {
  this.options.container.removeEventListener('scroll', this._scroll);
  window.removeEventListener('resize', this._scroll);
  this.attached = false;
}
function off(event, selector, handler) {
    var enterCallbacks = Object.keys(this.trackedElements[selector].enter || {});
    var leaveCallbacks = Object.keys(this.trackedElements[selector].leave || {});
    if ({}.hasOwnProperty.call(this.trackedElements, selector)) {
        if (handler) {
            if (this.trackedElements[selector][event]) {
                var callbackName = typeof handler === 'function' ? handler.name : handler;
                delete this.trackedElements[selector][event][callbackName];
            }
        } else {
            delete this.trackedElements[selector][event];
        }
    }
    if (!enterCallbacks.length && !leaveCallbacks.length) {
        delete this.trackedElements[selector];
    }
}
function on(event, selector, callback) {
    var allowed = ['enter', 'leave'];
    if (!event) throw new Error('No event given. Choose either enter or leave');
    if (!selector) throw new Error('No selector to track');
    if (allowed.indexOf(event) < 0) throw new Error(event + ' event is not supported');
    if (!{}.hasOwnProperty.call(this.trackedElements, selector)) {
        this.trackedElements[selector] = {};
    }
    this.trackedElements[selector].nodes = [];
    for (var i = 0, elems = document.querySelectorAll(selector); i < elems.length; i++) {
        var item = {
            isVisible: false,
            wasVisible: false,
            node: elems[i]
        };
        this.trackedElements[selector].nodes.push(item);
    }
    if (typeof callback === 'function') {
        if (!this.trackedElements[selector][event]) {
            this.trackedElements[selector][event] = {};
        }
        this.trackedElements[selector][event][callback.name || 'anonymous'] = callback;
    }
}
function observeDOM(obj, callback) {
    var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
    if (MutationObserver) {
        var obs = new MutationObserver(callback);
        obs.observe(obj, {
            childList: true,
            subtree: true
        });
    } else {
        obj.addEventListener('DOMNodeInserted', callback, false);
        obj.addEventListener('DOMNodeRemoved', callback, false);
    }
}
function OnScreen() {
    var _this = this;
    var options = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : { tolerance: 0, debounce: 100, container: window };
    this.options = {};
    this.trackedElements = {};
    Object.defineProperties(this.options, {
        container: {
            configurable: false,
            enumerable: false,
            get: function get() {
                var container = void 0;
                if (typeof options.container === 'string') {
                    container = document.querySelector(options.container);
                } else if (options.container instanceof HTMLElement) {
                    container = options.container;
                }
                return container || window;
            },
            set: function set(value) {
                options.container = value;
            }
        },
        debounce: {
            get: function get() {
                return parseInt(options.debounce, 10) || 100;
            },
            set: function set(value) {
                options.debounce = value;
            }
        },
        tolerance: {
            get: function get() {
                return parseInt(options.tolerance, 10) || 0;
            },
            set: function set(value) {
                options.tolerance = value;
            }
        }
    });
    Object.defineProperty(this, '_scroll', {
        enumerable: false,
        configurable: false,
        writable: false,
        value: this._debouncedScroll.call(this)
    });
    observeDOM(document.querySelector('body'), function () {
        Object.keys(_this.trackedElements).forEach(function (element) {
            _this.on('enter', element);
            _this.on('leave', element);
        });
    });
    this.attach();
}
Object.defineProperties(OnScreen.prototype, {
    _debouncedScroll: {
        configurable: false,
        writable: false,
        enumerable: false,
        value: debouncedScroll
    },
    attach: {
        configurable: false,
        writable: false,
        enumerable: false,
        value: attach
    },
    destroy: {
        configurable: false,
        writable: false,
        enumerable: false,
        value: destroy
    },
    off: {
        configurable: false,
        writable: false,
        enumerable: false,
        value: off
    },
    on: {
        configurable: false,
        writable: false,
        enumerable: false,
        value: on
    }
});
OnScreen.check = inViewport;
export default OnScreen;
