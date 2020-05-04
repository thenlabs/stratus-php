"use strict";

class StratusApp {

    constructor(controller, token) {
        this.controller = controller;
        this.token = token;
        this.classes = {};
        this.components = {};
    }

    getClass(id) {
        return this.classes[id];
    }

    addClass(id, classInstance) {
        this.classes[id] = classInstance;
    }
}
