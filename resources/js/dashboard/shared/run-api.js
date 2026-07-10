let _runnerFactory = null;

export function setRunnerFactory(factory) {
    _runnerFactory = factory;
}

export function ensureRunApi() {
    if (typeof _runnerFactory !== 'function') {
        throw new Error('ensureRunApi: runner factory not initialized');
    }
    return _runnerFactory();
}
