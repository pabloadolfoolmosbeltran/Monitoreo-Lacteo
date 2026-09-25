export function chooseSupplier(product, requested = null) {
  const suppliers = product?.proveedores ?? [];
  if (requested !== null && suppliers.some(supplier => Number(supplier.productor_id) === Number(requested))) {
    return Number(requested);
  }
  return suppliers.length === 1 ? Number(suppliers[0].productor_id) : null;
}

export function selectedSupplier(product, supplierId) {
  return (product?.proveedores ?? []).find(supplier => Number(supplier.productor_id) === Number(supplierId)) ?? null;
}

export function supplierStock(product, supplierId) {
  return Number(selectedSupplier(product, supplierId)?.stock ?? 0);
}

export function supplierPrice(product, supplierId) {
  return Number(selectedSupplier(product, supplierId)?.precio ?? 0);
}
