"use strict";

class StratusApp {

    constructor(controller, token) {
        this.controller = controller;
        this.token = token;
        this.classes = {};
        this.components = {};
        this.buffer = {};
    }

    getClass(id) {
        return this.classes[id];
    }

    addClass(id, classInstance) {
        this.classes[id] = classInstance;
    }

    getComponent(id) {
        return this.components[id];
    }

    addComponent(component) {
        this.components[component.id] = component;
    }

    dispatch(eventName) {
        const xhr = new XMLHttpRequest();
        xhr.lastResponseLen = 0;

        xhr.onprogress = event => {
            if (! event.currentTarget) return;

            let currentResponse = null;
            let responseBuffer = event.currentTarget.response;

            if (xhr.lastResponseLen === false) {
                currentResponse = responseBuffer;
                xhr.lastResponseLen = responseBuffer.length;
            } else {
                currentResponse = responseBuffer.substring(xhr.lastResponseLen);
                xhr.lastResponseLen = responseBuffer.length;
            }

            if ('string' === typeof(currentResponse)) {
                processMessage(currentResponse);
            }
        };

        xhr.onreadystatechange = () => {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                processMessage(xhr.responseText);
            }
        };

        xhr.open('POST', this.controller, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        const request = {
            token: this.token,
            componentData: this.buffer,
            eventName,
        };

        xhr.send(`stratus_message=` + JSON.stringify(request));
        this.buffer = {};
    }
}
