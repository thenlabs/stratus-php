"use strict";

class StratusApp {

    constructor() {
        this.classes = {};
    }

    getClass(id) {
        return this.classes[id];
    }

    addClass(id, classInstance) {
        this.classes[id] = classInstance;
    }
}
