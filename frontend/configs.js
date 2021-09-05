export const configs = {
    wsUrl: process.env.apiPort ? `ws://${window.location.hostname}:${process.env.apiPort}/` : 'ws://api/',
    apiUrl: process.env.apiUrl + '/api/',
};