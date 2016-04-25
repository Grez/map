$(function() {
	window.Pathfinding = $class({

		selector: '#mainMap',
		map: undefined,
		start: undefined,
		target: undefined,

		constructor: function(graph, start) {
			var self = this;
			this.map = $(this.selector);
			this.setStart(start);

			$(this.map).on('click', '.cell', function () {
				var target = self.getPositionFromCell($(this));
				self.setTarget(target);
			});
		},

		setStart: function (start) {
			this.start = start;
			var cell = this.getCellFromPosition(start);

			cell.addClass('start');
		},

		setTarget: function (target) {
			var self = this;
			var cell = this.getCellFromPosition(target);
			this.target = target;
			self.map.addClass('loading');

			this.erasePath();
			cell.addClass('target');

			var worker = new Worker('/main/map-js-worker');
			worker.onmessage = function (e) {
				var result = e.data;

				var weight = 0;
				for (var i = 0; i < result.length; i++) {
					var gridNode = result[i];
					var x = gridNode.x;
					var y = gridNode.y;
					$('.cell[data-virtual-x=' + x + '][data-virtual-y=' + y + ']').addClass('path');
					weight += result[i].weight;
				}

				self.map.removeClass('loading');
			};
			var data = { start: this.start, target: this.target };
			worker.postMessage(data);
		},

		erasePath: function () {
			this.map.find('.path').removeClass('path');
			this.map.find('.target').removeClass('target');
		},

		getCellFromPosition: function (position) {
			var virtualX = position[0];
			var virtualY = position[1];
			return this.map.find('.cell[data-virtual-x=' + virtualX + '][data-virtual-y=' + virtualY + ']');
		},

		getPositionFromCell: function (cell) {
			var virtualX = cell.data('virtualX');
			var virtualY = cell.data('virtualY');
			return [virtualX, virtualY];
		}

	});
});
