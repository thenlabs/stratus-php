"use strict";

class StratusApp {

    constructor() {
        this.classes = {};
    }

    getClass(id) {
        return this.classes[id];
    }

    addClass(id, c) {
        this.classes[id] = c;
    }
}
