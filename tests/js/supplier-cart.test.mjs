import test from 'node:test';
import assert from 'node:assert/strict';
import { chooseSupplier, supplierStock, supplierPrice } from '../../resources/js/supplier-cart.js';

const product = {
  proveedores: [
    { productor_id: 4, nombre: 'Finca A', precio: '11.50', stock: 3 },
    { productor_id: 9, nombre: 'Finca B', precio: '13.00', stock: 7 },
  ],
};

test('preselects the only supplier and requires a choice when several exist', () => {
  assert.equal(chooseSupplier({ proveedores: [product.proveedores[0]] }), 4);
  assert.equal(chooseSupplier(product), null);
  assert.equal(chooseSupplier(product, 9), 9);
  assert.equal(chooseSupplier(product, 99), null);
});

test('uses stock and price from the selected supplier', () => {
  assert.equal(supplierStock(product, 9), 7);
  assert.equal(supplierPrice(product, 9), 13);
  assert.equal(supplierStock(product, null), 0);
});
