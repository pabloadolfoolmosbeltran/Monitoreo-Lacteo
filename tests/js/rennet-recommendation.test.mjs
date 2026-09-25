import test from 'node:test';
import assert from 'node:assert/strict';
import {
    requiredRennet,
    rennetReferences,
} from '../../resources/js/rennet-recommendation.js';

test('calculates live and reference rennet amounts', () => {
    assert.equal(requiredRennet(12, 0.25), 3);
    assert.deepEqual(rennetReferences(0.25), [
        { liters: 1, amount: 0.25 },
        { liters: 5, amount: 1.25 },
        { liters: 10, amount: 2.5 },
    ]);
});

test('normalizes invalid or negative inputs without producing NaN', () => {
    assert.equal(requiredRennet(-5, 0.25), 0);
    assert.equal(requiredRennet(10, 'invalid'), 0);
    assert.deepEqual(rennetReferences(null), [
        { liters: 1, amount: 0 },
        { liters: 5, amount: 0 },
        { liters: 10, amount: 0 },
    ]);
});
