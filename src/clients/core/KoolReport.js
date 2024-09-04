var KoolReport = KoolReport || {};
KoolReport.load = KoolReport.load || {
    scripts: [],
    scriptCallbacks: [],
    links: [],
    linkCallbacks: [],
    doneCallbacks: [],
    resources: function (resources, cb) {
        if (resources.js) {
            this.js(resources.js, cb);
        }
        if (resources.css) {
            this.css(resources.css);
        }
    },
    js: function (sources, cb) {
        var now = sources.filter(function (value) {
            return typeof (value) == "string";
        });
        var next = sources.filter(function (value) {
            return typeof (value) == "object";
        });
        if (next.length == 0) {
            this.scriptCallbacks.push(cb);
            now.forEach(function (src) {
                this.registerScript(src);
            }.bind(this));
        }
        else {
            this.scriptCallbacks.push(function () {
                this.js(next[0], cb);
            }.bind(this));
            now.forEach(function (src) {
                this.registerScript(src);
            }.bind(this));
        }
        this.checkScriptsAndCallback();
    },
    registerScript: function (src) {
        if (typeof this.scripts[src] == "undefined") {
            var existedTag = false;

            if (typeof (window.jQuery) != 'undefined' && src.indexOf("/jquery.min.js") >= 0) {
                existedTag = true;
            }

            if (existedTag == false) {
                var onPageScripts = document.getElementsByTagName("script");
                for (var i = 0; i < onPageScripts.length; i++) {
                    if (onPageScripts[i].attributes["src"] && onPageScripts[i].attributes["src"].value == src) {
                        existedTag = true;
                        break;
                    }
                }
            }

            if (existedTag) {
                this.scripts[src] = 1;
            }
            else {
                var script = document.createElement("script");
                script.type = "text/javascript";
                script.src = src;
                script.onload = this.onScriptLoaded.bind(this);
                script.onerror = this.onScriptLoaded.bind(this);
                document.head.appendChild(script);
                this.scripts[src] = 0;
            }
        }
    },
    onScriptLoaded: function (e) {
        this.scripts[e.target.attributes["src"].value] = 1;
        this.checkScriptsAndCallback();
    },
    checkScriptsAndCallback: function () {
        var allLoaded = true;
        for (var src in this.scripts) {
            allLoaded &= this.scripts[src];
        }

        if (allLoaded) {
            var runCbs = this.scriptCallbacks;
            this.scriptCallbacks = [];
            runCbs.forEach(function (cb) {
                if (cb) {
                    try { cb(); }
                    catch (err) { console.log(err); }
                }
            });
            setTimeout(this.checkOnDone.bind(this), 2);
        }
    },
    onDone: function (cb) {
        this.doneCallbacks.push(cb);
        this.checkOnDone();
    },
    checkOnDone: function () {
        if (this.scriptCallbacks.length == 0) {
            var doneCbs = this.doneCallbacks;
            this.doneCallbacks = [];
            doneCbs.forEach(function (cb) {
                if (cb) {
                    try { cb(); }
                    catch (err) { console.log(err); }
                }
            });
        }
    },

    css: function (hrefs) {
        var now = hrefs.filter(function (href) {
            return typeof (href) == "string";
        });
        var next = hrefs.filter(function (href) {
            return typeof (href) == "object";
        });

        if (next.length == 0) {
            now.forEach(function (href) {
                this.registerLink(href);
            }.bind(this));
        }
        else {
            this.linkCallbacks.push(function () {
                this.css(next[0]);
            }.bind(this));
            now.forEach(function (href) {
                this.registerLink(href);
            }.bind(this));
        }
    },

    registerLink: function (href) {
        if (typeof this.links[href] == "undefined") {
            var links = document.getElementsByTagName("link");
            var found = false;
            for (var i = 0; i < links.length; i++) {
                if (!found && links[i].attributes["href"] && links[i].attributes["href"].value == href) {
                    found = true;
                    break;
                }
            }
            if (found) {
                this.links[href] = 1;
            }
            else {
                var link = document.createElement("link");
                link.rel = 'stylesheet';
                link.type = "text/css";
                link.href = href;
                link.onload = this.onLinkLoaded.bind(this);
                link.onerror = this.onLinkLoaded.bind(this);
                document.head.appendChild(link);
                this.links[href] = 0;
            }
        }
    },
    onLinkLoaded: function (e) {
        this.links[e.target.attributes["href"].value] = 1;
        this.checkLinksAndCallback();
    },
    checkLinksAndCallback: function () {
        var allLoaded = true;
        for (var href in this.links) {
            allLoaded &= this.links[href];
        }
        if (allLoaded) {
            var runCbs = this.linkCallbacks;
            this.linkCallbacks = [];
            runCbs.forEach(function (cb) {
                try { cb(); }
                catch (err) { console.log(err); }
            });
        }
    }
};

KoolReport.widget = (KoolReport.widget) ? KoolReport.widget : {
    init: function (resources, cb) {
        KoolReport.load.resources(resources, cb);
    },
    loadAndRunDataTables: function (jsonObject) {
        console.log('loadAndRunDataTables');
        // return;
        KoolReport.widget.init(
            jsonObject.resources,
            function () {
                var uniqueId = jsonObject.id
                var dtOptions = jsonObject.options;
                var fastRender = jsonObject.fastRender;
                if (fastRender) {
                    dtOptions.data = jsonObject.dataRows;
                }
                window[uniqueId + '_state'] = {};
                KoolReport.helper.ConvertToClientFunction.call(dtOptions, dtOptions);
                window[uniqueId] = $('#' + uniqueId).DataTable(dtOptions);
                window[uniqueId + '_data'] = {
                    id: jsonObject.id,
                    searchOnEnter: jsonObject.earchOnEnter ? 1 : 0,
                    searchMode: jsonObject.searchMode,
                    serverSide: jsonObject.serverSide ? 1 : 0,
                    serverSideInstantSearch: jsonObject.serverSideInstantSearch ? 1 : 0,
                    overrideSearchInput: jsonObject.overrideSearchInput ? 1 : 0,
                    rowDetailData: dtOptions.rowDetailData,
                    showColumnKeys: jsonObject.$showColumnKeys,
                    columns: jsonObject.columns,
                    editUrl: jsonObject.editUrl,
                    fastRender: fastRender,
                    rowDetailIcon: jsonObject.rowDetailIcon ? 1 : 0,
                    rowDetailSelector: jsonObject.rowDetailSelector,
                    clientRowSpanColumns: jsonObject.clientRowSpanColumns,
                    themeBase: jsonObject.themeBase,
                    rawData: jsonObject.rawData,
                };
                window['KR' + uniqueId] = KoolReport.KRDataTables.create(window[uniqueId + '_data']);

                KoolReport.helper.registerClientEvents(jsonObject);
                KoolReport.helper.runOnReady(jsonObject);
            }
        );
    },
    runGoogleChart: function (jsonObject) {
        console.log('runGoogleChart');
        KoolReport.helper.ConvertToClientFunction.call(window[jsonObject.name], jsonObject);

        window[jsonObject.name] = new KoolReport.google.chart(
            jsonObject.chartType, jsonObject.name, jsonObject.cKeys, jsonObject.data, jsonObject.options, jsonObject.loader);

        if (jsonObject.pointerOnHover) {
            window[jsonObject.name].pointerOnHover = true;
        }
        
        KoolReport.helper.registerClientEvents(jsonObject);
        KoolReport.helper.runOnReady(jsonObject);
    },
    runD3Chart: function (jsonObject) {
        window[jsonObject.name] = new KoolReport.d3.C3Chart(jsonObject.settings);
        
        KoolReport.helper.registerClientEvents(jsonObject);
        KoolReport.helper.runOnReady(jsonObject);
    },
    runD3ChartWaterfall: function (jsonObject) {
        window[jsonObject.name] = new KoolReport.d3.Waterfall(jsonObject.name, jsonObject.settings);
        window[jsonObject.name].draw();
        
        KoolReport.helper.registerClientEvents(jsonObject);
        KoolReport.helper.runOnReady(jsonObject);
    },
    runChartJS: function (jsonObject) {
        console.log('KoolReport.js runChartJS');
        window[jsonObject.name] = new ChartJS(jsonObject.name, jsonObject.settings);
        
        KoolReport.helper.registerClientEvents(jsonObject);
        KoolReport.helper.runOnReady(jsonObject);
    },
    runApexCharts: function (jsonObject) {
        // console.log('runApexCharts');
        var name = jsonObject.settings.name;
        window[name + '_settings'] = jsonObject.settings;
        window[name] = KoolReport.ApexCharts.create(jsonObject.settings);
        
        KoolReport.helper.registerClientEvents(jsonObject);
        KoolReport.helper.runOnReady(jsonObject);
    },
    loadAndRunApexCharts: function (jsonObject) {
        // console.log('loadAndRunApexCharts');
        KoolReport.widget.init(jsonObject.resources, function () {
            var name = jsonObject.name;
            window[name + '_settings'] = jsonObject.settings;
            window[name] = KoolReport.ApexCharts.create(jsonObject.settings);
            
            KoolReport.helper.registerClientEvents(jsonObject);
            KoolReport.helper.runOnReady(jsonObject);
        })
    },
    loadAndRunDrillDown: function (jsonObject) {
        console.log('loadAndRunDrillDown: ', jsonObject);
        KoolReport.widget.init(jsonObject.resources, function () {
            var name = jsonObject.name;
            window[name] = new KoolReport.drilldown.DrillDown(name, jsonObject.options);

            KoolReport.helper.registerClientEvents(jsonObject);
            KoolReport.helper.runOnReady(jsonObject);
        })
    },
    loadAndRunPivotTable: function (jsonObject) {
        console.log('loadAndRunPivotTable: ', jsonObject);
        KoolReport.widget.init(
            jsonObject.resources,
            function () {
                var rowCollapseLevels = jsonObject.rowCollapseLevels;
                rowCollapseLevels.sort(function (a, b) { return b - a; });
                jsonObject.rowCollapseLevels = rowCollapseLevels;
                var colCollapseLevels = jsonObject.colCollapseLevels;
                colCollapseLevels.sort(function (a, b) { return b - a; });
                jsonObject.colCollapseLevels = colCollapseLevels;
                window[jsonObject.name] = KoolReport.PivotTable.create(jsonObject);

                KoolReport.helper.runOnReady(jsonObject);
            }
        );
    },
    loadAndRunPivotMatrix: function (jsonObject) {
        console.log('loadAndRunPivotMatrix: ', jsonObject);
        KoolReport.widget.init(
            jsonObject.resources,
            function () {
                window[jsonObject.name] = KoolReport.PivotMatrix.create(jsonObject);

                KoolReport.helper.runOnReady(jsonObject);
            }
        );
    },
    loadAndRunVisualQuery: function (jsonObject) {
        var name = jsonObject.name;
        vqTheme = jsonObject.vqTheme;
        KoolReport.widget.init(jsonObject.resources, function () {
            window[name + '_data'] = {
                name: name,
                tableNames: jsonObject.tableNames,
                tables: jsonObject.tables,
                tableAliases: jsonObject.tableAliases,
                tableLinks: jsonObject.tableLinks,
                defaultValue: jsonObject.defaultValue,
                value: jsonObject.value,
                separator: jsonObject.separator,
            }
            window[name] = KoolReport.VisualQuery.create(window[name + '_data']);
            
            KoolReport.helper.runOnReady(jsonObject);

            document.body.style.visibility = 'visible';
            document.body.style.opacity = 1;
        });
    }
};

KoolReport.helper = (KoolReport.helper) ? KoolReport.helper : {
    ConvertToClientFunction: function (obj) {
        for (var p in obj) {
            if (obj.hasOwnProperty(p)) {
                var val = obj[p];
                if (typeof val === 'string') {
                    val = val.trim();
                    // console.log('val: ', val);
                    var regex = /([^\s]*)\((.*)\)$/;
                    var found = val.match(regex);
                    if (found) {
                        // console.log('found: ', found);
                        // var funcNames = val.slice(0, val.length - 2);
                        var funcNames = found[1];
                        var thisObj;
                        try {
                            thisObj = JSON.parse(found[2]);
                        } catch (e) {
                            console.log('client function\'s this obj is not JSON')
                        }
                        funcNames = funcNames.split(".");
                        // console.log('funcNames: ', funcNames);
                        var func = window;
                        funcNames.forEach(function (funcName) {
                            func = func[funcName];
                        })
                        if (typeof func === 'function') {
                            // obj[p] = func.bind(this);
                            obj[p] = func.bind(thisObj || this);
                        }
                    }
                } else if (typeof val === 'object') {
                    KoolReport.helper.ConvertToClientFunction.call(this, obj[p]);
                }
            }
        }
    },

    registerClientEvents: function(jsonObject) {
        var name = jsonObject.name;
        var obj = { clientEvents: jsonObject.clientEvents };
        KoolReport.helper.ConvertToClientFunction.call(null, obj);
        var clientEvents = obj.clientEvents;
        var registerFunc = window[name].registerEvent ? 'registerEvent' : 'on';
        for (var event in clientEvents) {
            var func = clientEvents[event];
            if (typeof func === 'function') {
                window[name][registerFunc](event, func);
            }
            if (typeof window[func] === 'function') {
                window[name][registerFunc](event, window[func]);
            }
        }
    },

    runOnReady: function(jsonObject) {
        var obj = { onReady: jsonObject.onReady };
        KoolReport.helper.ConvertToClientFunction.call(null, obj);
        // console.log('obj.onReady: ', obj.onReady);
        var onReady = obj.onReady;
        if (typeof onReady === 'function') {
            onReady();
        }
        if (typeof window[onReady] === 'function') {
            window[onReady]();
        }
    },

    html: function (selector, html) {
        console.log('KoolReport.widget.html func: ', selector);
        var el = document.querySelector(selector);
        el.innerHTML = html;

        // var jsFilesList = el.querySelectorAll('krwidget_js_files');
        // var jsonObjectList = el.querySelectorAll('krwidget_json_object');
        // var jsonScriptList = el.querySelectorAll('krwidget_json_script');
        // jsFilesList.forEach(function(jsFiles, i) {
        //     jsFiles = JSON.parse(jsFiles.innerHTML.trim());
        //     var jsonObject = jsonObjectList[i].innerHTML.trim();
        //     jsonObject = JSON.parse(jsonObject);
        //     jsonScriptList[i].innerHTML = "";
        //     KoolReport.widget.init(jsFiles, function() {
        //         KoolReport.widget['run' + jsonObject.widgetType](jsonObject);
        //     });
        // })

        // var jsFilesList = el.querySelectorAll('dashboard_js_files');
        // var jsonObjectList = el.querySelectorAll('dashboard_json_object');
        // var jsonScriptList = el.querySelectorAll('dashboard_json_script');
        // jsFilesList.forEach(function(jsFiles, i) {
        //     jsFiles = JSON.parse(jsFiles.innerHTML.trim());
        //     var jsonObject = jsonObjectList[i].innerHTML.trim();
        //     jsonObject = JSON.parse(jsonObject);
        //     jsonScriptList[i].innerHTML = "";
        //     KoolReport.widget.init(jsFiles, function() {
        //         KoolReport.dashboard.widgets['run' + jsonObject.widgetType](jsonObject);
        //     });
        // })
    },
    executeScript: function (selector) {
        console.log('KoolReport.widget.executeScript func: ', selector);
        var x = document.querySelector(selector).getElementsByTagName("script");
        for (var i = 0; i < x.length; i++) {
            console.log('execute script: ', x[i].outerHTML);
            eval(x[i].text);
        }
    },
    executeJsonScript: function (selector) {
        console.log('KoolReport.widget.executeJsonScript func: ', selector);
        var jsonScriptEls = Array.from(document.querySelector(selector).querySelectorAll("json_script"));
        jsonScriptEls.forEach(function (jsonScriptEl) {
            var jsonScript = jsonScriptEl.innerHTML.trim();
            if (!jsonScript) return;
            var jsonScript = JSON.parse(jsonScript);
            var functions = KoolReport.helper.JsonToFunc(jsonScript);
            functions.forEach(function (func) {
                if (func.isNew) {
                    var result = new func();
                } else {
                    var result = func();
                }
                if (func.returnName) window[func.returnName] = result;
            })
        })
    },
    executeJsonCommand: function (selector) {
        console.log('KoolReport.widget.executeJsonCommand func: ', selector);
        var jsonScriptEls = Array.from(document.querySelector(selector).querySelectorAll("json_command"));
        jsonScriptEls.forEach(function (jsonScriptEl) {
            var jsonCommand = jsonScriptEl.innerHTML.trim();
            if (!jsonCommand) return;
            var jsonCommand = JSON.parse(jsonCommand);
            var func = KoolReport.helper.JsonItemToFunc(jsonCommand);
            func();
        })
    },

    JsonItemToFunc: function (jsonItem) {
        if (jsonItem === null || !jsonItem["function"]) return jsonItem;
        var func = jsonItem["function"];
        var value = jsonItem["value"];
        var args = jsonItem["arguments"] || [];
        var returnName = jsonItem["return"];
        var isNew = jsonItem["isNew"];
        var f;
        if (func === "{anonymous}") {
            var json = jsonItem["json"];
            f = function () {
                // console.log('anonymous func evoke')
                KoolReport.helper.JsonToFunc(json).forEach(function (funcItem) {
                    // funcItem();
                    if (funcItem.isNew) {
                        var result = new funcItem();
                    } else {
                        var result = funcItem();
                    }
                    if (funcItem.returnName) {
                        var returnObj = window;
                        var returnNameParts = funcItem.returnName.split(".");
                        returnNameParts.forEach(function (returnNamePart) {
                            returnObj[returnNamePart] = returnObj[returnNamePart] || {};
                            return returnObj = returnObj[returnNamePart];
                        })
                        returnObj = result;
                    }
                });
            };
        } else if (func === "{anonymousCall}") {
            var json = jsonItem["json"];
            f = function () {
                KoolReport.helper.JsonToFunc(json).forEach(function (funcItem) {
                    funcItem();
                });
            }();
        } else if (typeof func === 'string' && func) {
            funcNames = func.split(".");
            f = window;
            funcNames.forEach(function (funcName) {
                f = f[funcName];
            })
            args = KoolReport.helper.JsonToFunc(args);
        } else if (typeof value !== 'undefined') {
            if (returnName) {
                var returnObj = window;
                var returnNameParts = funcItem.returnName.split(".");
                returnNameParts.forEach(function (returnNamePart) {
                    returnObj[returnNamePart] = returnObj[returnNamePart] || {};
                    return returnObj = returnObj[returnNamePart];
                })
                returnObj = value;
            } else {
                return value;
            }
        }
        // console.log('f: ', f);
        // console.log('args: ', args);
        var f = f ? f.bind(null, ...args) : null;
        if (f && isNew) f.isNew = isNew;
        if (f && returnName) f.returnName = returnName;
        return f;
    },

    JsonToFunc: function (json) {
        if (!json.map) {
            json = [json];
        }
        return json.map(function (jsonItem) {
            return KoolReport.helper.JsonItemToFunc(jsonItem);
        })
    },

    DrillDownNext: function (params) {
        console.log('DrillDownNext');
        if (this.type === 'googlechart') {
            window[this.name].next(params.selectedRow);
        } else if (this.type === 'koolphptable') {
            window[this.name].next(params.rowData);
        } else if (this.type === 'chartjs') {
            window[this.name].next(params.selectedRow);
        } else if (this.type === 'd3') {
            window[this.name].next(params.selectedRow);
        }
    }
};






