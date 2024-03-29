$(function() {
	$("#country-form").on("submit", function(event) {
		event.preventDefault();
		
		$("#message").empty();
		$("#output tbody").empty();
		$("#totals").empty();
		
		$.ajax({
			"type"    : "POST",
			"url"     : '/endpoint/',
			"dataType": 'json',
			xhrFields: {
				withCredentials: false
			},
			"data"    : {
				"name" : $("#country-name").val(),
				"searchBy" : $("#searchBy").val()
			},
			"success" : function(data) {
				if(data.responseCode === -1) {
					$("#message").html(data.responseMessage);
					return;
				}
				
				var countCountries = 0;
				var regions = {};
				var subregions = {};
				
				$.each(data.results, function(key,value) {
					countCountries += 1;
					
					if (value.region) {
						regions[value.region] = !regions[value.region] ? 1 : regions[value.region] + 1;
					}
					
					if (value.subregion) {
						subregions[value.subregion] = !subregions[value.subregion] ? 1 : subregions[value.subregion] + 1;
					}
					
					var languages = $.map(value.languages, function(val, i) {
						return val;
					});
					
					$("#output tbody").append(
						"<tr>"
						+ "<td>"+value.name.official+"</td>"
						+ "<td>"+value.cca2+"</td>"
						+ "<td>"+value.cca3+"</td>"
						+ "<td><img src=\""+value.flags.png+"\" width=\"100\"></td>"
						+ "<td>"+value.region+"</td>"
						+ "<td>"+value.subregion+"</td>"
						+ "<td>"+value.population+"</td>"
						+ "<td>"+languages.join(", ")+"</td>"
						+ "</tr>"
					);
				});
				
				var regionsText = "";
				var first = true;
				$.each(regions, function(key, value) {
					regionsText += (!first ? ", " : "") + key+"("+value+")"
					first = false;
				});
				
				var subregionsText = "";
				first = true;
				$.each(subregions, function(key, value) {
					subregionsText += (!first ? ", " : "") + key+"("+value+")"
					first = false;
				});
				
				
				$("#totals").html(
					"<ul>"
					+ "<li>Total Countries: "+countCountries+"</li>"
					+ "<li>Regions: "+regionsText+"</li>"
					+ "<li>Subregions: "+subregionsText+"</li>"
					+ "</ul>");
			},
			"error" : function() {
				$("#message").html("Something is not working as intended!");
			}
		});
	})
});