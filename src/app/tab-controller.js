export class TabController
{
    constructor(container)
    {
        this.domTabs = container;       //Container
        this.tabs = [];                 //List of tabs
        this._lastIndex = 0;            //Index of the last tab
        this._firstOpen = true;         //Flag for when it is the first tab opened
        this.selected = null;           //Currently selected tab

        this.onTabSelected = function(tab){}
        this.onTabDeselected = function(tab) {}
        this.onTabOpen = function (tab) {}
        this.onTabClose = function(tab) {}
    }

    /** Count the number of tabs that are currently opened */
    count() { return Object.keys(this.tabs).length; }

    /** Opens a new empty tab. */
    openEmpty() { }

    /** Opens a tab with a title and id. */
    open(title, id, state = {}, select = true)
    {
        //Abort early because we are already open.
        if (this.tabs[id]) 
            return this.tabs[id];

        if (this._firstOpen) {            
            this.domTabs.innerHTML = "";
            this._firstOpen = false;
        }

        let self = this;
        let closable = state == null || !state.unclosable;

        let li = document.createElement('li');
        let a = document.createElement('a');
        a.innerHTML = "<span id='title'>" + title + "</span>";

        if (closable) {
            let cross = document.createElement('span');
            cross.id = "close";
            cross.innerHTML = '<span class="icon is-small"><i class="far fa-times" aria-hidden="true"></i></span>';
            a.appendChild(cross);
            cross.addEventListener("click", function(){ self.close(id); });
        }

        li.appendChild(a);
        this.domTabs.appendChild(li);
        a.onmousedown = function(e){ if (e.which == 2) self.close(id); else self.select(id); return false; };

        this.tabs[id] = {
            id: id, 
            dom: li, 
            title: title, 
            closable: closable,
            state: state, 
            active: false, 
            dirty: false,
            index: this._lastIndex++,
            
            setTitle: function(title) {
                this.title = title;
                this.dom.firstElementChild.firstElementChild.innerText=this.title + (this.dirty ? "*" : "");
            },

            makeDirty: function() {
                this.dirty = true;
                this.setTitle(this.title);
            },

            makeClean: function() {
                this.dirty = false;
                this.setTitle(this.title);
            },

            isDirty: function() { return this.dirty; },
            
        };

        if (this.onTabOpen) this.onTabOpen(this.tabs[id]);
        if (select) this.select(id);
        return this.tabs[id];
    }

    /** Checks if the tab exists */
    isOpen(id) {        
        if (!this.tabs[id]) return false;
        return true;
    }

    /** Selects a tab by id */
    select(id)
    {
        if (!this.tabs[id]) return false;
        if (this.tabs[id].active) return true;

        let old_tab = null;
        let new_tab = null;
        let was_active = false;
        for(let i in this.tabs)
        {
            if (this.tabs[i].id == id) 
            { 
                this.tabs[i].dom.classList.add('is-active'); 
                this.tabs[i].active = true; 
                new_tab = this.tabs[i];
            }
            else                  
            { 
                this.tabs[i].dom.classList.remove('is-active'); 
                was_active = this.tabs[i].active;
                this.tabs[i].active = false; 
                if (was_active) old_tab = this.tabs[i];
            }
        }

        this.selected = new_tab;
        if (old_tab && this.onTabDeselected) this.onTabDeselected(old_tab);
        if (new_tab && this.onTabSelected) this.onTabSelected(new_tab);
        return true;
    }

    /** Selects the next tab */
    next() {
        if (!this.selected) return false;

        let sort = this.tabs.sort(function(a, b) { return a.index - b.index; });
        let tab = null;
        for(let i in sort)
        {
            if (tab == this.selected) 
            {
                this.select(sort[i].id);
                return true;
            }

            tab = sort[i];
        }

        return false;
    }

    /** Selects the previous tab */
    previous() {
        let sort = this.tabs.sort(function(a, b) { return a.index - b.index; });
        let tab = null;
        for(let i in sort)
        {
            if (sort[i] == this.selected && tab != null) 
            {
                this.select(tab.id);
                return true;
            }

            tab = sort[i];
        }

        return false;
    }

    /** Closes a tab. If no ID is supplied, then the current tab is closed. */
    close(id = null)
    {
        if (id == null && this.selected == null) return false;
            id = this.selected.id;

        if (!this.tabs[id]) return false;

        if (this.onTabClose && !this.onTabClose(this.tabs[id]))
            return false;

        if (this.onTabDeselected)
            this.onTabDeselected(this.tabs[id]);

        let neighbour = null;
        let deleted = false;
        let sort = this.tabs.sort(function(a, b) { return a.index - b.index; });
        let tab = null;
        let wasActive = false;

        for(let i in sort)
        {
            tab = sort[i];
            if (tab.id == id)
            {
                //WE are the object, so delete ourself and trigger the delete
                tab.dom.remove();
                deleted = true;
            }
            else
            {
                //We are just a neighbour
                neighbour = tab.id;
            }

            //Break once we find a good substitute and we have been deleted.
            if (neighbour != null && deleted) break;
        }

        wasActive = this.tabs[id] != null && this.tabs[id].active;
        delete this.tabs[id];

        //Open the neighbour tab (if any)
        if (neighbour == null) 
        {
            this.openEmpty();
        }
        else if (wasActive)
        {
            this.select(neighbour);
        }
    }
}