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
			var html = '<div class="start-description"><b>Yup! That\'s you!</b><br>';
			cell.append(html);

			this.centerToStart();
		},

		centerToStart: function () {
			var halfHeight = $(this.selector).height() / 2;
			var halfWidth = $(this.selector).width() / 2;

			var top = $(this.selector + ' .start').position().top;
			if (top > halfHeight) {
				this.map.scrollTop(top - halfHeight);
			}

			var left = $(this.selector + ' .start').position().left;
			if (left > halfWidth) {
				this.map.scrollLeft(left - halfWidth);
			}
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
				console.log(e);
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
			var data = { map: localStorage.getItem('mapJs'), start: this.start, target: this.target };
			worker.postMessage(data);
		},

		erasePath: function () {
			this.map.find('.path').removeClass('path');
			this.map.find('.target').removeClass('target');
		},

		getCellFromPosition: function (position) {
			var virtualX = position[0];
			var virtualY = position[1];
			return this.map.find(' .cell[data-virtual-x=' + virtualX + '][data-virtual-y=' + virtualY + ']');
		},

		getPositionFromCell: function (cell) {
			var virtualX = cell.data('virtualX');
			var virtualY = cell.data('virtualY');
			return [virtualX, virtualY];
		}

	});
});
