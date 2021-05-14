'use strict';
var Datepicker = (function() {
	var $datepicker = $('.datepicker');
	function init($this) {
		var options = {
			disableTouchKeyboard: true,
			autoclose: false
		};
		$this.datepicker(options);
	}
	if ($datepicker.length) {
		$datepicker.each(function() {
			init($(this));
		});
	}
})();
'use strict';
var CopyIcon = (function() {
	var $element = '.btn-icon-clipboard',
		$btn = $($element);
	function init($this) {
		$this.tooltip().on('mouseleave', function() {
			$this.tooltip('hide');
		});
		var clipboard = new ClipboardJS($element);
		clipboard.on('success', function(e) {
			$(e.trigger)
				.attr('title', 'Copied!')
				.tooltip('_fixTitle')
				.tooltip('show')
				.attr('title', 'Copy to clipboard')
				.tooltip('_fixTitle')
			e.clearSelection()
		});
	}
	if ($btn.length) {
		init($btn);
	}
})();
'use strict';
var FormControl = (function() {
	var $input = $('.form-control');
	function init($this) {
		$this.on('focus blur', function(e) {
        $(this).parents('.form-group').toggleClass('focused', (e.type === 'focus' || this.value.length > 0));
    }).trigger('blur');
	}
	if ($input.length) {
		init($input);
	}
})();
var $map = $('#map-canvas'),
    map,
    lat,
    lng,
    color = "#5e72e4";
function initMap() {
    map = document.getElementById('map-canvas');
    lat = map.getAttribute('data-lat');
    lng = map.getAttribute('data-lng');
    var myLatlng = new google.maps.LatLng(lat, lng);
    var mapOptions = {
        zoom: 12,
        scrollwheel: false,
        center: myLatlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        styles: [{"featureType":"administrative","elementType":"labels.text.fill","stylers":[{"color":"#444444"}]},{"featureType":"landscape","elementType":"all","stylers":[{"color":"#f2f2f2"}]},{"featureType":"poi","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"road","elementType":"all","stylers":[{"saturation":-100},{"lightness":45}]},{"featureType":"road.highway","elementType":"all","stylers":[{"visibility":"simplified"}]},{"featureType":"road.arterial","elementType":"labels.icon","stylers":[{"visibility":"off"}]},{"featureType":"transit","elementType":"all","stylers":[{"visibility":"off"}]},{"featureType":"water","elementType":"all","stylers":[{"color":color},{"visibility":"on"}]}]
    }
    map = new google.maps.Map(map, mapOptions);
    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        animation: google.maps.Animation.DROP,
        title: 'Hello World!'
    });
    var contentString = '<div class="info-window-content"><h2>Argon Dashboard</h2>' +
        '<p>A beautiful Dashboard for Bootstrap 4. It is Free and Open Source.</p></div>';
    var infowindow = new google.maps.InfoWindow({
        content: contentString
    });
    google.maps.event.addListener(marker, 'click', function() {
        infowindow.open(map, marker);
    });
}
if($map.length) {
    google.maps.event.addDomListener(window, 'load', initMap);
}
'use strict';
var Navbar = (function() {
	var $nav = $('.navbar-nav, .navbar-nav .nav');
	var $collapse = $('.navbar .collapse');
	var $dropdown = $('.navbar .dropdown');
	function accordion($this) {
		$this.closest($nav).find($collapse).not($this).collapse('hide');
	}
    function closeDropdown($this) {
        var $dropdownMenu = $this.find('.dropdown-menu');
        $dropdownMenu.addClass('close');
    	setTimeout(function() {
    		$dropdownMenu.removeClass('close');
    	}, 200);
	}
	$collapse.on({
		'show.bs.collapse': function() {
			accordion($(this));
		}
	})
	$dropdown.on({
		'hide.bs.dropdown': function() {
			closeDropdown($(this));
		}
	})
})();
var NavbarCollapse = (function() {
	var $nav = $('.navbar-nav'),
		$collapse = $('.navbar .collapse');
	function hideNavbarCollapse($this) {
		$this.addClass('collapsing-out');
	}
	function hiddenNavbarCollapse($this) {
		$this.removeClass('collapsing-out');
	}
	if ($collapse.length) {
		$collapse.on({
			'hide.bs.collapse': function() {
				hideNavbarCollapse($collapse);
			}
		})
		$collapse.on({
			'hidden.bs.collapse': function() {
				hiddenNavbarCollapse($collapse);
			}
		})
	}
})();
'use strict';
var noUiSlider = (function() {
	if ($(".input-slider-container")[0]) {
			$('.input-slider-container').each(function() {
					var slider = $(this).find('.input-slider');
					var sliderId = slider.attr('id');
					var minValue = slider.data('range-value-min');
					var maxValue = slider.data('range-value-max');
					var sliderValue = $(this).find('.range-slider-value');
					var sliderValueId = sliderValue.attr('id');
					var startValue = sliderValue.data('range-value-low');
					var c = document.getElementById(sliderId),
							d = document.getElementById(sliderValueId);
					noUiSlider.create(c, {
							start: [parseInt(startValue)],
							connect: [true, false],
							range: {
									'min': [parseInt(minValue)],
									'max': [parseInt(maxValue)]
							}
					});
					c.noUiSlider.on('update', function(a, b) {
							d.textContent = a[b];
					});
			})
	}
	if ($("#input-slider-range")[0]) {
			var c = document.getElementById("input-slider-range"),
					d = document.getElementById("input-slider-range-value-low"),
					e = document.getElementById("input-slider-range-value-high"),
					f = [d, e];
			noUiSlider.create(c, {
					start: [parseInt(d.getAttribute('data-range-value-low')), parseInt(e.getAttribute('data-range-value-high'))],
					connect: !0,
					range: {
							min: parseInt(c.getAttribute('data-range-value-min')),
							max: parseInt(c.getAttribute('data-range-value-max'))
					}
			}), c.noUiSlider.on("update", function(a, b) {
					f[b].textContent = a[b]
			})
	}
})();
'use strict';
var Popover = (function() {
	var $popover = $('[data-toggle="popover"]'),
		$popoverClass = '';
	function init($this) {
		if ($this.data('color')) {
			$popoverClass = 'popover-' + $this.data('color');
		}
		var options = {
			trigger: 'focus',
			template: '<div class="popover ' + $popoverClass + '" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
		};
		$this.popover(options);
	}
	if ($popover.length) {
		$popover.each(function() {
			init($(this));
		});
	}
})();
'use strict';
var ScrollTo = (function() {
	var $scrollTo = $('.scroll-me, [data-scroll-to], .toc-entry a');
	function scrollTo($this) {
		var $el = $this.attr('href');
        var offset = $this.data('scroll-to-offset') ? $this.data('scroll-to-offset') : 0;
		var options = {
			scrollTop: $($el).offset().top - offset
		};
        $('html, body').stop(true, true).animate(options, 600);
        event.preventDefault();
	}
	if ($scrollTo.length) {
		$scrollTo.on('click', function(event) {
			scrollTo($(this));
		});
	}
})();
'use strict';
var Tooltip = (function() {
	var $tooltip = $('[data-toggle="tooltip"]');
	function init() {
		$tooltip.tooltip();
	}
	if ($tooltip.length) {
		init();
	}
})();
'use strict';
var Charts = (function() {
	var $toggle = $('[data-toggle="chart"]');
	var mode = 'light';
	var fonts = {
		base: 'Open Sans'
	}
	var colors = {
		gray: {
			100: '#f6f9fc',
			200: '#e9ecef',
			300: '#dee2e6',
			400: '#ced4da',
			500: '#adb5bd',
			600: '#8898aa',
			700: '#525f7f',
			800: '#32325d',
			900: '#212529'
		},
		theme: {
			'default': '#172b4d',
			'primary': '#5e72e4',
			'secondary': '#f4f5f7',
			'info': '#11cdef',
			'success': '#2dce89',
			'danger': '#f5365c',
			'warning': '#fb6340'
		},
		black: '#12263F',
		white: '#FFFFFF',
		transparent: 'transparent',
	};
	function chartOptions() {
		var options = {
			defaults: {
				global: {
					responsive: true,
					maintainAspectRatio: false,
					defaultColor: (mode == 'dark') ? colors.gray[700] : colors.gray[600],
					defaultFontColor: (mode == 'dark') ? colors.gray[700] : colors.gray[600],
					defaultFontFamily: fonts.base,
					defaultFontSize: 13,
					layout: {
						padding: 0
					},
					legend: {
						display: false,
						position: 'bottom',
						labels: {
							usePointStyle: true,
							padding: 16
						}
					},
					elements: {
						point: {
							radius: 0,
							backgroundColor: colors.theme['primary']
						},
						line: {
							tension: .4,
							borderWidth: 4,
							borderColor: colors.theme['primary'],
							backgroundColor: colors.transparent,
							borderCapStyle: 'rounded'
						},
						rectangle: {
							backgroundColor: colors.theme['warning']
						},
						arc: {
							backgroundColor: colors.theme['primary'],
							borderColor: (mode == 'dark') ? colors.gray[800] : colors.white,
							borderWidth: 4
						}
					},
					tooltips: {
						enabled: false,
						mode: 'index',
						intersect: false,
						custom: function(model) {
							var $tooltip = $('#chart-tooltip');
							if (!$tooltip.length) {
								$tooltip = $('<div id="chart-tooltip" class="popover bs-popover-top" role="tooltip"></div>');
								$('body').append($tooltip);
							}
							if (model.opacity === 0) {
								$tooltip.css('display', 'none');
								return;
							}
							function getBody(bodyItem) {
								return bodyItem.lines;
							}
							if (model.body) {
								var titleLines = model.title || [];
								var bodyLines = model.body.map(getBody);
								var html = '';
								html += '<div class="arrow"></div>';
								titleLines.forEach(function(title) {
									html += '<h3 class="popover-header text-center">' + title + '</h3>';
								});
								bodyLines.forEach(function(body, i) {
									var colors = model.labelColors[i];
									var styles = 'background-color: ' + colors.backgroundColor;
									var indicator = '<span class="badge badge-dot"><i class="bg-primary"></i></span>';
									var align = (bodyLines.length > 1) ? 'justify-content-left' : 'justify-content-center';
									html += '<div class="popover-body d-flex align-items-center ' + align + '">' + indicator + body + '</div>';
								});
								$tooltip.html(html);
							}
							var $canvas = $(this._chart.canvas);
							var canvasWidth = $canvas.outerWidth();
							var canvasHeight = $canvas.outerHeight();
							var canvasTop = $canvas.offset().top;
							var canvasLeft = $canvas.offset().left;
							var tooltipWidth = $tooltip.outerWidth();
							var tooltipHeight = $tooltip.outerHeight();
							var top = canvasTop + model.caretY - tooltipHeight - 16;
							var left = canvasLeft + model.caretX - tooltipWidth / 2;
							$tooltip.css({
								'top': top + 'px',
								'left': left + 'px',
								'display': 'block',
								'z-index': '100'
							});
						},
						callbacks: {
							label: function(item, data) {
								var label = data.datasets[item.datasetIndex].label || '';
								var yLabel = item.yLabel;
								var content = '';
								if (data.datasets.length > 1) {
									content += '<span class="badge badge-primary mr-auto">' + label + '</span>';
								}
								content += '<span class="popover-body-value">' + yLabel + '</span>' ;
								return content;
							}
						}
					}
				},
				doughnut: {
					cutoutPercentage: 83,
					tooltips: {
						callbacks: {
							title: function(item, data) {
								var title = data.labels[item[0].index];
								return title;
							},
							label: function(item, data) {
								var value = data.datasets[0].data[item.index];
								var content = '';
								content += '<span class="popover-body-value">' + value + '</span>';
								return content;
							}
						}
					},
					legendCallback: function(chart) {
						var data = chart.data;
						var content = '';
						data.labels.forEach(function(label, index) {
							var bgColor = data.datasets[0].backgroundColor[index];
							content += '<span class="chart-legend-item">';
							content += '<i class="chart-legend-indicator" style="background-color: ' + bgColor + '"></i>';
							content += label;
							content += '</span>';
						});
						return content;
					}
				}
			}
		}
		Chart.scaleService.updateScaleDefaults('linear', {
			gridLines: {
				borderDash: [2],
				borderDashOffset: [2],
				color: (mode == 'dark') ? colors.gray[900] : colors.gray[300],
				drawBorder: false,
				drawTicks: false,
				lineWidth: 0,
				zeroLineWidth: 0,
				zeroLineColor: (mode == 'dark') ? colors.gray[900] : colors.gray[300],
				zeroLineBorderDash: [2],
				zeroLineBorderDashOffset: [2]
			},
			ticks: {
				beginAtZero: true,
				padding: 10,
				callback: function(value) {
					if (!(value % 10)) {
						return value
					}
				}
			}
		});
		Chart.scaleService.updateScaleDefaults('category', {
			gridLines: {
				drawBorder: false,
				drawOnChartArea: false,
				drawTicks: false
			},
			ticks: {
				padding: 20
			},
			maxBarThickness: 10
		});
		return options;
	}
	function parseOptions(parent, options) {
		for (var item in options) {
			if (typeof options[item] !== 'object') {
				parent[item] = options[item];
			} else {
				parseOptions(parent[item], options[item]);
			}
		}
	}
	function pushOptions(parent, options) {
		for (var item in options) {
			if (Array.isArray(options[item])) {
				options[item].forEach(function(data) {
					parent[item].push(data);
				});
			} else {
				pushOptions(parent[item], options[item]);
			}
		}
	}
	function popOptions(parent, options) {
		for (var item in options) {
			if (Array.isArray(options[item])) {
				options[item].forEach(function(data) {
					parent[item].pop();
				});
			} else {
				popOptions(parent[item], options[item]);
			}
		}
	}
	function toggleOptions(elem) {
		var options = elem.data('add');
		var $target = $(elem.data('target'));
		var $chart = $target.data('chart');
		if (elem.is(':checked')) {
			pushOptions($chart, options);
			$chart.update();
		} else {
			popOptions($chart, options);
			$chart.update();
		}
	}
	function updateOptions(elem) {
		var options = elem.data('update');
		var $target = $(elem.data('target'));
		var $chart = $target.data('chart');
		parseOptions($chart, options);
		toggleTicks(elem, $chart);
		$chart.update();
	}
	function toggleTicks(elem, $chart) {
		if (elem.data('prefix') !== undefined || elem.data('prefix') !== undefined) {
			var prefix = elem.data('prefix') ? elem.data('prefix') : '';
			var suffix = elem.data('suffix') ? elem.data('suffix') : '';
			$chart.options.scales.yAxes[0].ticks.callback = function(value) {
				if (!(value % 10)) {
					return prefix + value + suffix;
				}
			}
			$chart.options.tooltips.callbacks.label = function(item, data) {
				var label = data.datasets[item.datasetIndex].label || '';
				var yLabel = item.yLabel;
				var content = '';
				if (data.datasets.length > 1) {
					content += '<span class="popover-body-label mr-auto">' + label + '</span>';
				}
				content += '<span class="popover-body-value">' + prefix + yLabel + suffix + '</span>';
				return content;
			}
		}
	}
	if (window.Chart) {
		parseOptions(Chart, chartOptions());
	}
	$toggle.on({
		'change': function() {
			var $this = $(this);
			if ($this.is('[data-add]')) {
				toggleOptions($this);
			}
		},
		'click': function() {
			var $this = $(this);
			if ($this.is('[data-update]')) {
				updateOptions($this);
			}
		}
	});
	return {
		colors: colors,
		fonts: fonts,
		mode: mode
	};
})();
var OrdersChart = (function() {
	var $chart = $('#chart-orders');
	var $ordersSelect = $('[name="ordersSelect"]');
	function initChart($chart) {
		var ordersChart = new Chart($chart, {
			type: 'bar',
			options: {
				scales: {
					yAxes: [{
						ticks: {
							callback: function(value) {
								if (!(value % 10)) {
									return value
								}
							}
						}
					}]
				},
				tooltips: {
					callbacks: {
						label: function(item, data) {
							var label = data.datasets[item.datasetIndex].label || '';
							var yLabel = item.yLabel;
							var content = '';
							if (data.datasets.length > 1) {
								content += '<span class="popover-body-label mr-auto">' + label + '</span>';
							}
							content += '<span class="popover-body-value">' + yLabel + '</span>';
							return content;
						}
					}
				}
			},
			data: {
				labels: ['Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
				datasets: [{
					label: 'Sales',
					data: [25, 20, 30, 22, 17, 29]
				}]
			}
		});
		$chart.data('chart', ordersChart);
	}
	if ($chart.length) {
		initChart($chart);
	}
})();
'use strict';
var SalesChart = (function() {
	var $chart = $('#chart-sales');
	function init($chart) {
		var salesChart = new Chart($chart, {
			type: 'line',
			options: {
				scales: {
					yAxes: [{
						gridLines: {
							color: Charts.colors.gray[900],
							zeroLineColor: Charts.colors.gray[900]
						},
						ticks: {
							callback: function(value) {
								if (!(value % 10)) {
									return '$' + value + 'k';
								}
							}
						}
					}]
				},
				tooltips: {
					callbacks: {
						label: function(item, data) {
							var label = data.datasets[item.datasetIndex].label || '';
							var yLabel = item.yLabel;
							var content = '';
							if (data.datasets.length > 1) {
								content += '<span class="popover-body-label mr-auto">' + label + '</span>';
							}
							content += '<span class="popover-body-value">$' + yLabel + 'k</span>';
							return content;
						}
					}
				}
			},
			data: {
				labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
				datasets: [{
					label: 'Performance',
					data: [0, 20, 10, 30, 15, 40, 20, 60, 60]
				}]
			}
		});
		$chart.data('chart', salesChart);
	};
	if ($chart.length) {
		init($chart);
	}
})();
