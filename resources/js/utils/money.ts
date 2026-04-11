/**
 * Formats a money amount in smallest currency unit (agorot) to ILS.
 */
export const formatPrice = (amount: number): string => {
    return `₪${(amount / 100).toLocaleString('he-IL', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
};
