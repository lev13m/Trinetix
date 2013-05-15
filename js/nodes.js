$(function() {
	bindEvents();
});

function bindEvents() {
	$('a').live('click', function() {
		openClose(this, getNextLevel);
		return false;
	});

	$('a').live('contextmenu', function() {
		openClose(this, getAllChild);
		return false;
	});
}

function openClose(el, onOpen, onClose) {
	var type = $(el).data('type');
	if (type == 0) return;

	var span = $(el).find('span');
	var st = span.text();
	if (st === '+') {
		span.text('-');
		$(el).next('.child').show();
		if ($.isFunction(onOpen)) onOpen(el);
	}
	else {
		span.text('+');
		$(el).next('.child').hide();
		if ($.isFunction(onClose)) onClose(el);
	}
}

function getNextLevel(el) {
	var nodeId = $(el).data('id');

	$.ajax({
		url: '/next-level.php',
		data: {
			node_id: nodeId
		},
		dataType: "json",
		type: "post",
		success: function(resp) {
			renderLevel(nodeId, resp);
		}
	});
}

function getAllChild(el) {
	var nodeId = $(el).data('id');

	$.ajax({
		url: '/get-all.php',
		data: {
			node_id: nodeId
		},
		dataType: "json",
		type: "post",
		success: function(resp) {
			recursion(nodeId, resp.children);
		}
	});
}

function recursion(nodeId, data) {

	renderLevel(nodeId, data, true);

	for (var i in data) {
		if (data[i].children !== undefined) recursion(data[i].id, data[i].children);
	}
}

function renderLevel(nodeId, children, isOpen) {
	var container = $('a[data-id="' + nodeId + '"]').next('.child');
	var html;

	container.empty();
	for (var i in children) {
		html = '<div>';
		html += '<a href="#" data-type="' + children[i].count_child + '" data-id="' + children[i].id + '">';
		if (isOpen === true) html += '<span>-</span>';
		else html += '<span>' + (children[i].count_child == 0 ? '-' : '+') + '</span>';
		html += children[i].name + ' (' + children[i].count_child + ')';
		html += '</a>';
		html += '<div class="child"></div>';
		html += '</div>';

		container.append(html);
	}
}