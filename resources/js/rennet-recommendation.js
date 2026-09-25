export function requiredRennet(liters, perLiter) {
    const numericLiters = Number(liters);
    const numericDose = Number(perLiter);
    const safeLiters = Number.isFinite(numericLiters) && numericLiters > 0
        ? numericLiters
        : 0;
    const safeDose = Number.isFinite(numericDose) && numericDose > 0
        ? numericDose
        : 0;

    return Math.round(safeLiters * safeDose * 100) / 100;
}

export function rennetReferences(perLiter) {
    return [1, 5, 10].map(liters => ({
        liters,
        amount: requiredRennet(liters, perLiter),
    }));
}
