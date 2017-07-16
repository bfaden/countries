$(function() {
	$("#country-form").on("submit", function(event) {
		event.preventDefault();
		
		$("#message").empty();
		$("#output tbody").empty();
				$("#totals").empty();
				
		if(!$("#country-name").val()) {
			$("#message").html("Please enter a country name.");
			return;
		}
		
		$.ajax({
			"type"    : "POST",
			"url"     : '/endpoint/'+$("#country-name").val(),
			"dataType": 'json',
			xhrFields: {
				withCredentials: false
			},
			"data"    : {
				"orderBy" : $("#sort").val()
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
						return val.name;
					});
					
					$("#output tbody").append(
						"<tr>"
						+ "<td>"+value.name+"</td>"
						+ "<td>"+value.alpha2Code+"</td>"
						+ "<td>"+value.alpha3Code+"</td>"
						+ "<td><img src=\""+value.flag+"\" width=\"100\"></td>"
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
				$("#message").html("this doesn't work");
			}
		});
	})
});