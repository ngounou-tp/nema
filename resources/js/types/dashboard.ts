/*
 * Props of the dashboards (App\Services\DashboardService): raw values,
 * enum keys and ISO dates, translated and formatted in the interface.
 */

export type OrderStatus = 'pending' | 'confirmed' | 'delivered' | 'cancelled';

export type DashboardOrder = {
    id: number;
    product: string;
    filiere: string;
    counterpart: string;
    quantityKg: number;
    totalXaf: number;
    status: OrderStatus;
    createdAt: string | null;
};

export type DashboardProduct = {
    id: number;
    name: string;
    filiere: string;
    quantityKg: number;
    priceXaf: number;
    isAvailable: boolean;
};

export type DashboardDiagnosis = {
    id: number;
    crop: string | null;
    status: 'pending' | 'completed' | 'uncertain' | 'failed';
    confidence: number | null;
    createdAt: string | null;
};

export type TopSupplier = {
    id: number;
    name: string;
    village: string;
    region: string | null;
    averageRating: number;
    reviewsCount: number;
    score: number | null;
};
