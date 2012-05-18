function initialize_spm_field_ask_qi(){
	if(parent == null) return;
	var fields = parent.spm_wf_editor.extra.ask.js.getAllValidProperties();
	
	QIHelper.prototype.addRestrictionInput2 = QIHelper.prototype.addRestrictionInput;
	QIHelper.prototype.addRestrictionInput = function () {
		this.addRestrictionInput2();
		var newrow = $('dialoguecontent_pvalues').rows[$('dialoguecontent_pvalues').rows.length - 2];
		var cell = newrow.insertCell(2);
		
		var oSelect = document.createElement("SELECT");
		var idx = newrow.id.substring(5);
		oSelect.id = "use_field_r" + idx;
		var optOff = 0;
		var opt = new Option('choose field ...', '');
		opt.style.width="100%";
		oSelect.options[optOff] = opt;
		for(var field in fields) {
			++ optOff;
			opt = new Option(field, field);
			oSelect.options[optOff] = opt;
		}
		cell.appendChild(oSelect);
		Event.observe( $(oSelect), "change", function(o) {
			if(this.value == '') return;
			$("input_r" + idx).value = fields[this.value];
		});
	};
/*
	QIHelper.prototype.initFromQueryString2 = QIHelper.prototype.initFromQueryString;
	QIHelper.prototype.initFromQueryString =  function(ask) {
		this.initFromQueryString2(ask);
	};
*/
	// source copied from QIHelper.js
	QIHelper.prototype.addPropertyGroup = function() {
		// check if user clicked on add, while prop information is not yet loaded.
		if (!$('input_c1')) return;
		var pname='';
		var propInputFields = $('dialoguecontent').getElementsByTagName('input');
		for (var i = 0, n = propInputFields.length; i < n; i++) {
			pname += propInputFields[i].value + '.';
		}
		pname = pname.replace(/\.$/,'');
		var subqueryIds = Array();
		if (pname == "") { // no name entered?
			$('qistatus').innerHTML = gLanguage
					.getMessage('QI_ENTER_PROPERTY_NAME');
			this.updateHeightBoxcontent();
		} else {
			var pshow = $('input_c1').checked; // show in results?
			// when show in results is checked, add label and unit if they exist
			var colName = (pshow) ? $('input_c3').value : null;
			var showUnit = (pshow) ? $('input_c4').value : null;
			var pmust = $('input_c2').checked; // value must be set?
			var arity = this.proparity;
			var selector = this.getPropertyValueSelector();
			// create propertyGroup
			var pgroup = new PropertyGroup(escapeQueryHTML(pname), arity,
					pshow, pmust, this.propIsEnum, this.enumValues, selector, showUnit, colName);
			pgroup.setUnits(this.propUnits);
			var allValueRows = $('dialoguecontent_pvalues').rows.length;
			// there is no value restriction
			if (selector != -2) {
				var paramname = $('dialoguecontent').rows[$('dialoguecontent').rows.length -2].cells[1].innerHTML;
				paramname = paramname.replace(gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ', '');
				// no subquery, so add a dumy value
				if (selector == -1) {
					if (arity == 2)
						pgroup.addValue(paramname, '=', '*');
					else {
						for (s = 1; s < arity; s++) {
							pgroup.addValue($('dialoguecontent_pvalues').rows[s].cells[0].innerHTML, '=', '*');
						}
					}
				}
				else {
					if (selector < this.nextQueryId) // Subquery does exists
														// already
						paramvalue = selector;
					else { // Sub Query does not yet exist
						paramvalue = this.nextQueryId;
						subqueryIds.push(this.nextQueryId);
						this.addQuery(this.activeQueryId, pname);
					}
					/* STARTLOG */
					if (window.smwhgLogger) {
						var logstr = "Add subquery to query, property '"
								+ pname + "'";
						smwhgLogger.log(logstr, "QI", "query_subquery_added");
					}
					/* ENDLOG */
					pgroup.addValue('subquery', '=', paramvalue);
				}
			} else {
			for ( var i = 0; i < allValueRows; i++) {
				// for a property several values were selected but if in the
				// list a value
				// in the middle has been deleted, the inputX doesn't exist
				// anymore, so skip this one and
				// continue with the next one. For example if the 3rd value was
				// deleted (input5) then:
				// variable i contains the logical value i.e. input3 = 3, input4
				// = 4, input6 = 6
				// variable cHtmlRow is the current html row, i.e. input3 = 3,
				// input4 = 4, input6 = 5
				try {
					// works on normal input fiels as well as on selection lists
					var paramvalue = $('dialoguecontent_pvalues').rows[i].cells[2].firstChild.value;
					if(paramvalue != "") {
						paramvalue = "{{{" + paramvalue + "|" + $('dialoguecontent_pvalues').rows[i].cells[3].firstChild.value + "}}}";
					} else {
						paramvalue = $('dialoguecontent_pvalues').rows[i].cells[3].firstChild.value;
					}
				} catch (e) {continue;}
				// no value is replaced by "*" which means all values
				paramvalue = paramvalue == "" ? "*" : paramvalue; 
				var paramname;
				if (arity == 2) {
					paramname = $('dialoguecontent').rows[$('dialoguecontent').rows.length -2].cells[1].innerHTML;
					paramname = paramname.replace(gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ', '');
				}
				else {
					paramname = $('dialoguecontent_pvalues').rows[i].cells[0].innerHTML;
				}
				var restriction = $('dialoguecontent_pvalues').rows[i].cells[1].firstChild.value;
				var unit = null;
				try {
					unit = $('dialoguecontent_pvalues').rows[i].cells[3].firstChild.nextSibling.value;
				} catch (e) {};
				// add a value group to the property group
				pgroup.addValue(paramname, restriction, escapeQueryHTML(paramvalue), unit);
			}
			}
			/* STARTLOG */
			if (window.smwhgLogger) {
				var logstr = "Add property " + pname + " to query";
				smwhgLogger.log(logstr, "QI", "query_property_added");
			}
			/* ENDLOG */
			this.activeQuery.addPropertyGroup(pgroup, subqueryIds,
					this.loadedFromId); // add the property group to the query
			this.emptyDialogue();
			this.updateColumnPreview();
			$('qistatus').innerHTML = gLanguage.getMessage('QI_PROP_ADDED_SUCCESSFUL')
			// if the property contains a subquery, set the active query now to this subquery
			if (selector > 0) this.setActiveQuery(selector);
		}
	};
	
	QIHelper.prototype.loadPropertyDialogue = function(id) {
		this.newPropertyDialogue(false);
		this.loadedFromId = id;
		var prop = this.activeQuery.getPropertyGroup(id);
		var vals = prop.getValues();
		this.proparity = prop.getArity();
		var selector = prop.getSelector();
		this.propUnits = prop.getUnits();

		var propChain = unescapeQueryHTML(prop.getName()).split('.'); // fill input
																	// filed with
																	// name
		$('input_p0').value=propChain[0];
		for (var i = 1, n = propChain.length; i < n; i++) {
			$('dialoguecontent').rows[i * 2 - 1].cells[1].innerHTML =
				gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' +
				gLanguage.getMessage('QI_PAGE');
			this.addPropertyChainInput(propChain[i]);

		}
		this.propname = propChain[propChain.length - 1];
		this.completePropertyDialogue();
		// check box value must be set
		$('input_c2').checked = prop.mustBeSet();

		// set correct property type under last property input
		var typeRow = $('dialoguecontent').rows.length-2;
		if (this.proparity > 2) {
			$('dialoguecontent').rows[typeRow].cells[1].innerHTML =
				gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' +
				gLanguage.getMessage('TYPE_RECORD') ;
			this.toggleSubquery(false);
		} else {
			// get type of property, if it's a subquery then type is page
			this.propTypename = (selector >= 0) ? gLanguage.getMessage('QI_PAGE') : vals[0][0];
			$('dialoguecontent').rows[typeRow].cells[1].innerHTML =
					gLanguage.getMessage('QI_PROPERTY_TYPE') + ': ' + this.propTypename;
			if (this.propTypename != gLanguage.getMessage('QI_PAGE'))
				this.toggleSubquery(false);
			else
				this.toggleAddchain(true);
		}
		// set property is shown and colum name (these are empty for properties in subqueries)
		$('input_c1').checked = prop.isShown(); // check box if appropriate
		$('input_c3').value = prop.getColName();
		$('input_c3d').style.display= prop.isShown()
			? (Prototype.Browser.IE) ? 'inline' : null : 'none';

		// if we have a subquery set the selector correct and we are done
		if (selector >= 0) {
			document.getElementsByName('input_c1').disabled = "disabled";
			document.getElementsByName('input_r0')[2].checked = true;
			document.getElementsByName('input_r0')[2].value = selector;
			$('usesub_text').style.display="block";
			this.toggleAddchain(false);
		}
		else {
			if (this.activeQueryId == 0) {
				if (prop.supportsUnits() && this.proparity == 2) {
					$('input_c4').value = prop.getShowUnit();
					for (var i = 0; i < this.propUnits[0].length; i++) {
						$('input_c4').options[i]= 
							new Option(this.propUnits[0][i],this.propUnits[0][i]);
						if (prop.getShowUnit() == this.propUnits[0][i])
							$('input_c4').options[i].selected = "selected";
					}
					$('input_c4d').style.display= prop.isShown()
						? Prototype.Browser.IE ? 'inline' : null : 'none';
				}
			} else {
				$('input_c1').disabled = "disabled";
			}
			// if the selector is set to "restict value" then make the restictions visible
			if (selector == -2) {
				document.getElementsByName('input_r0')[1].checked = true;
				$('dialoguecontent_pvalues').style.display = "inline";
			}
			// load enumeration values
			if (prop.isEnumeration()) {
				this.propIsEnum = true;
				this.enumValues = prop.getEnumValues();
			}
			var acChange=false;
			var rowOffset = 0;
			// if arity > 2 then add the first row under the radio buttons without input field
			if (this.proparity > 2) {
				var newrow = $('dialoguecontent_pvalues').insertRow(-1);
				var cell = newrow.insertCell(-1);
				cell.innerHTML = gLanguage.getMessage('QI_PROPERTYVALUE');
				rowOffset++;
			}
			for (var i = 0, n = vals.length; i < n; i++) {
				var numType = 0;
				var currRow = i + rowOffset;
				if (this.numTypes[vals[0][0].toLowerCase()]) { // is it a numeric type?
					numType = 1;
					this.propTypetype = '_num';
				}
				else if (vals[0][0] == gLanguage.getMessage('TYPE_STRING')) {
					numType = 2;
					this.propTypetype = '_str';
				}
				if (vals[0][0] == gLanguage.getMessage('TYPE_DATE')) {
					this.propTypetype = '_dat';
				}
				this.addRestrictionInput();
				$('dialoguecontent_pvalues').rows[currRow].cells[1].innerHTML =
					this.createRestrictionSelector(vals[i][1], false, numType);
				// deactivate autocompletion
				if (!acChange)
					autoCompleter.deregisterAllInputs();
				acChange = true;
				
				// add unit selection, do this for all properties, even in subqueries
				try {
					var propUnits = prop.getUnits();
					var uIdx = (this.proparity == 2) ? 0 : i;
					var oSelect = $('dialoguecontent_pvalues').rows[currRow].cells[3]
						.firstChild.nextSibling;
					for (var k = 0, m = propUnits[uIdx].length; k < m; k++) {
						oSelect.options[k] = new Option(propUnits[uIdx][k], propUnits[uIdx][k]);
						if (propUnits[uIdx][k] == vals[i][3])
							oSelect.options[k].selected="selected";
					}
				} catch(e) {};
				if (this.proparity > 2) {
					$('dialoguecontent_pvalues').rows[currRow].cells[0].innerHTML= vals[i][0];
					$('dialoguecontent_pvalues').rows[currRow].cells[0].style.fontWeight="normal";
				}
				if (vals[i][2] != '*') // if a real value is set and not the placeholder for no value.
					var v = vals[i][2].unescapeHTML();
					var reg = /^\{\{\{([^|]+)\|(.*)\}\}\}$/;
					var r = reg.exec( v );
					if(r != null) {
						$('use_field_r'+(i+1)).value = r[1].trim();
						$('input_r'+(i+1)).value = r[2].trim();
					} else {
						$('input_r'+(i+1)).value = v;
					}
			}
			if (acChange) autoCompleter.registerAllInputs();
		}
		$('qidelete').style.display = "inline";
		
		if (!prop.isEnumeration()) this.restoreAutocompletionConstraints();
	};
	QIHelper.prototype.previewResultPrinter2 = QIHelper.prototype.previewResultPrinter;
	QIHelper.prototype.previewResultPrinter = function() {
		var getValsFunc = PropertyGroup.prototype.getValues;
		PropertyGroup.prototype.getValues = function() {
			var _values = [];
			var reg = /^\{\{\{([^|]+)\|(.*)\}\}\}$/;
			for(var i=0;i<this.values.length;++i) {
				var r = reg.exec( this.values[i][2] );
				if(r != null) {
					_values.push([ this.values[i][0], this.values[i][1], r[2].trim(), this.values[i][3] ]);
				} else {
					_values.push(this.values[i]);
				}
			}
			return _values;
		};
		this.previewResultPrinter2();
		PropertyGroup.prototype.getValues = getValsFunc;
	};
	
	// copied from SMWHalo 1.5.1, do not use 1.5.6
	QIHelper.prototype.parseQueryString = function() {
        var sub = this.queryPartsFromInitByAsk;

        // properties that must be shown in the result
        var pMustShow = this.applyOptionParams(sub[0]);

        // run over all query strings and start parsing
       for (f = 0; f < sub.length; f++) {
        // set current query to active, do this manually (treeview is not
        // updated)
        this.activeQuery = this.queries[f];
        this.activeQueryId = f;
        // extact the arguments, i.e. all between [[...]]
        var args = sub[f].split(/\]\]\s*\[\[/);
        // remove the ]] from the last element
        args[args.length - 1] = args[args.length - 1].substring(0,
                args[args.length - 1].indexOf(']]'));
        // and [[ from the first element
        args[0] = args[0].replace(/^\s*\[\[/, '');
        this.handleQueryString(args, f, pMustShow);
       }
        this.setActiveQuery(0); // set main query to active
        this.updateTree();      // show new tree
        this.updateColumnPreview(); // update sort selection
       this.updatePreview(); // update result preview
    };
	

	document.getElementById('fullAskText').style.width = "100%";
	var query = parent.spm_wf_editor.extra.ask.js.getLastAsk();
	if( ('function' == typeof $$) && $$('#askQI #query4DiscardChanges').length > 0 ) {
		$$('#askQI #query4DiscardChanges')[0].innerHTML = query;
	} else {
		document.getElementById('query4DiscardChanges').innerHTML = query;
	}
	qihelper.initFromQueryString(query);
}
Event.observe(window, 'load', initialize_spm_field_ask_qi);

function update_wf_field_ask_qi(){
	if(parent == null) return;
	
	qihelper.switchTab(3);
	var query = '';
	if( ('function' == typeof $$) && $$('#askQI #query4DiscardChanges').length > 0 ) {
		query = $$('#askQI #query4DiscardChanges')[0].innerHTML;
	} else {
		query = document.getElementById('query4DiscardChanges').innerHTML;
	}
	parent.jQuery.fancybox.close();
	parent.spm_wf_editor.extra.ask.js.updateAskObj( query );
}