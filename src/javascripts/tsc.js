function trie_search_catalog(search_bar) {
    this.search_bar = search_bar;
    this.form = document.getElementById("search_bar_form");
    this.root;

    //Set input autocomplete off so previous inputs do not show up
    this.search_bar.autocomplete = "off";

    this.search_bar.oninput = function (event) {
        let suggestion_div = this.parentElement;
        let input_text = this.value;
        let words = (input_text).split(" ");
        if (typeof words !== 'undefined') {
            //remove empty words (from removed spaces)
            while (typeof words[words.length - 1] !== 'undefined' && words[words.length - 1].length == 0) {
                words.pop();
            }
            if (words.length > 0) {
                let suggestions = tsc.get_suggestions((words[words.length - 1]).toLowerCase());
                let div = suggestion_div.getElementsByTagName("div")[0];
                div.innerHTML = "";
                suggestion_focus = undefined;
                for (let i = 0; i < suggestions.length; i++) {
                    //Create suggestion container divs with text inside:
                    let suggestion = document.createElement("div");
                    suggestion.className = "text_suggestion";
                    let sug_div = document.createElement("div");
                    sug_div.appendChild(document.createTextNode(input_text));
                    sug_text = document.createElement("strong");
                    sug_text.appendChild(document.createTextNode(suggestions[i]));
                    sug_div.appendChild(sug_text);
                    suggestion.appendChild(sug_div);
                    let input_value = document.createElement("input");
                    input_value.value = input_text + suggestions[i];
                    input_value.type = "hidden";
                    suggestion.appendChild(input_value);
                    suggestion.onmousedown = function (event) {
                        if (event.button == 0) {
                            let search_bar = document.getElementById("search_bar");
                            let phrase = (suggestion.getElementsByTagName("input")[0]).value;
                            search_bar.value = phrase;
                            suggestion.parentElement.innerHTML = "";
                        }
                    }
                    //Add suggestion to list
                    div.appendChild(suggestion);
                }
            }
        }
    };

    this.search_bar.onblur = function () {
        (this.parentElement.getElementsByTagName("div")[0]).innerHTML = "";
    }

    this.search_bar.onkeydown = function (event) {
        if (event.keyCode == 13 || event.which == 13) {
            if (suggestion_focus === undefined) {
                console.log("searching for '" + this.value + "' ...");
                if (this.value === undefined || this.value.length == 0) {
                    //Clear search bar
                    clear_search();
                    //Get all items
                    search();
                } else {
                    //Get searched items
                    tsc.search(this.value);
                }
                this.onblur();
            } else {
                //call arrow navigation function
                suggestion_arrow_naviagtion(this, event);
                suggestion_focus = undefined;
                this.onblur();
            }
        } else {
            //call arrow navigation function
            suggestion_arrow_naviagtion(this, event);
        }
    }

    this.build = function (str) {
        this.root = new tsc_node();
        let open = 1;
        let current = this.root;
        for (let i = str.indexOf("<") + 1; i < str.length; i++) {
            let char = str.charAt(i);
            switch (char) {
                case "<":
                    let node_str = str.substring(i + 1, str.indexOf("=", i + 1));
                    let new_node = new tsc_node(current, node_str);
                    current.child.push(new_node);
                    current = new_node;
                    open++;
                    break;
                case "'":
                    //Add references
                    let end_all_refs = str.indexOf(":", i + 1);
                    while (i < end_all_refs) {
                        let end_ref = str.indexOf(",", i + 1);
                        if (end_ref > end_all_refs || end_ref < i) {
                            end_ref = end_all_refs;
                        }
                        let key = str.substring(i + 1, end_ref);
                        if (key.length > 0) current.ref.push(key);
                        i = end_ref;
                    }
                    //Add next letter references
                    let end_index = str.indexOf("'", i + 1);
                    while (i < end_index) {
                        let end_next = str.indexOf(",", i + 1);
                        if (end_next > end_index || end_next < i) {
                            end_next = end_index;
                        }
                        let key = str.substring(i + 1, end_next);
                        if (key.length > 0) current.next.push(key);
                        i = end_next;
                    }
                    break;
                case ">":
                    current = current.prev;
                    open--;
                    break;
            }
        }
        console.log(open == 0 ? "all nodes closed" : "" + open + " nodes still open");
    }

    this.search = function () {
        var str = this.search_bar.value.toLowerCase();
        if (str === undefined || str.length == 0) return;

        //Split the search into words
        let words = str.split(" ");

        var results = {};
        for (let i = 0; i < words.length; i++) {
            results = this.root.search(words[i], results);
        }

        results_string = "";
        for (var key in results) {
            results_string = results_string + ", " + results[key];
        }

        //Sort the results by relevance
        var min = -1;
        var max = 0;
        for (var key in results) {
            if (results[key] > max) max = results[key];
            if (results[key] < min || min == -1) min = results[key];
        }

        var duplicate_indicies = new Array(max - min + 1);
        var total = 0;
        for (var key in results) {
            let i = results[key] - min;
            if (duplicate_indicies[i] === undefined) duplicate_indicies[i] = 0;
            else duplicate_indicies[i]++;
            total++;
        }

        var index_offset = (new Array(max - min + 1)).fill(0);
        var count = 0;
        for (let i = 0; i < duplicate_indicies.length - 1; i++) {
            let offset = duplicate_indicies[i];
            count += (offset === undefined ? 0 : offset + 1);
            index_offset[i + 1] = count;
        }

        var sorted_results = new Array(total);
        for (var key in results) {
            let key_idx = results[key] - min;
            let i = index_offset[key_idx] + duplicate_indicies[key_idx];
            duplicate_indicies[key_idx]--;
            sorted_results[i] = key;
        }

        var results_div = this.form.getElementsByTagName("div")[0];
        results_div.innerHTML = "";
        //POST the search result keys
        for (let i = total - 1; i >= 0; i--) {
            let input = document.createElement("input");
            input.type = 'hidden';
            input.name = "search_key[" + (total - i - 1) + "]";
            input.value = sorted_results[i];
            results_div.appendChild(input);
        }

        //call the search function with the results
        search(results_div); // ***** destination_page is set on a per-page basis! Make sure to set it on any new pages! *****
    }

    this.get_suggestions = function (str) {
        return this.root.get_suggestions(str);
    }
}

//Set suggestion focus to 0
var suggestion_focus = undefined;

//suggestion_arrow_navigation puts the selected suggestions in focus when the user uses arrow keys
function suggestion_arrow_naviagtion(elem, event) {
    let suggestions = elem.parentElement.getElementsByClassName("text_suggestion");
    if (suggestions !== undefined) {
        if (event.keyCode == 38 || event.keyCode == 40 || event.which == 38 || event.which == 40) {
            if (suggestion_focus === undefined) {
                suggestion_focus = 0;
            } else {
                if (event.keyCode == 38 || event.which == 38) suggestion_focus--;
                else suggestion_focus++;
            }
            //Keep focus in bounds
            suggestion_focus = (suggestion_focus >= suggestions.length ? suggestions.length - 1 : (suggestion_focus < 0 ? 0 : suggestion_focus));
            for (let i = 0; i < suggestions.length; i++) {
                suggestions[i].classList.remove("text_suggestion_active");
            }
            suggestions[suggestion_focus].classList.add("text_suggestion_active");
        } else if (event.keyCode == 13 || event.which == 13) {
            //suggestion has been selected
            if (suggestion_focus !== undefined) {
                triggerMouseEvent(suggestions[suggestion_focus], "mousedown");
            }
        }
    }
}

function triggerMouseEvent(node, eventType) {
    var clickEvent = document.createEvent('MouseEvents');
    clickEvent.initEvent(eventType, true, true);
    node.dispatchEvent(clickEvent);
}

function tsc_node(prev, str) {
    this.prev = prev;
    this.str = str;
    this.ref = new Array();
    this.child = new Array();
    this.next = new Array();

    this.is_root = function () {
        return (this.prev === null || this.prev === undefined) ? true : false;
    }

    this.search = function (str, results, depth) {
        if (results === undefined) results = {};
        if (depth === undefined) depth = 0;
        let matched = false;
        for (var i in this.child) {
            let child = this.child[i];
            for (let c = 0; c < str.length && c < child.str.length; c++) {
                if (str.charAt(c) == child.str.charAt(c)) {
                    matched = true;
                    if (c == child.str.length - 1) {
                        //increment the reference count
                        results = child.transfer_refs(results, depth + c + 1);
                        //Search the children
                        if (c + 1 < str.length) {
                            var trimmed_str = str.substring(c + 1, str.length);
                            results = child.search(trimmed_str, results, depth + c + 1);
                        }
                    } else {
                        //Add child's references to make sure partially matched words are also accounted for
                        results = child.transfer_refs(results, 1);
                    }
                } else break;
            }
            if (matched) break;
        }
        return results;
    }

    this.get_suggestions = function (str, results) {
        if (results === undefined) results = Array(0);
        for (var i in this.child) {
            let child = this.child[i];
            for (let c = 0; c < str.length && c < child.str.length; c++) {
                if (str.charAt(c) == child.str.charAt(c)) {
                    if (c == str.length - 1) {
                        //End of str found
                        //Add children suggestions with offset to not add what already matches
                        child.add_suggestions("", results, c + 1);
                        return results;
                    } else if (c == child.str.length - 1) {
                        //End of node found
                        child.get_suggestions(str.substring(c + 1, str.length), results);
                    }
                } else if (c == 0) {
                    //If first letter didn't match, then break
                    break;
                }
            }
        }
        return results;
    }

    this.add_suggestions = function (prev_str, results, offset = 0) {
        let str = prev_str;
        let str_add = this.str.substr(offset, this.str.length);
        str += (str_add === false ? "" : str_add);
        if (this.ref.length > 0) {
            //node is end of some word so add to results
            results.push(str);
            if (results.length >= 10) return results;
            //Add next word reccomendation results as well
            for (var i in this.next) {
                results.push(str + " " + this.next[i]);
                if (results.length >= 10) return results;
            }
        }
        if (this.child.length > 0) {
            for (var i in this.child) {
                this.child[i].add_suggestions(str, results);
            }
        }
    }

    this.transfer_refs = function (results, depth) {
        for (var key in this.ref) {
            if (results[this.ref[key]] === undefined) results[this.ref[key]] = 0;
            results[this.ref[key]] += (depth);
        }
        return results;
    }
}

function clear_search() {
    document.getElementById("search_results").innerHTML = "";
}