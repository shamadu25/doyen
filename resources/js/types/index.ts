export interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    employee_id?: string;
    hourly_rate?: number;
    phone?: string;
    created_at: string;
    updated_at: string;
}

export interface Customer {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email?: string;
    phone?: string;
    mobile?: string;
    address_line_1?: string;
    address_line_2?: string;
    city?: string;
    county?: string;
    postcode?: string;
    notes?: string;
    outstanding_balance: number;
    vehicles_count?: number;
    vehicles?: Vehicle[];
    invoices?: Invoice[];
    created_at: string;
    updated_at: string;
}

export interface Vehicle {
    id: number;
    customer_id: number;
    registration: string;
    make: string;
    model: string;
    variant?: string;
    year?: number;
    vin?: string;
    fuel_type?: string;
    colour?: string;
    engine_size?: string;
    mileage?: number;
    mot_expiry?: string;
    tax_due_date?: string;
    service_due_date?: string;
    dvla_data?: Record<string, any>;
    customer?: Customer;
    mot_tests?: MotTest[];
    bookings?: Booking[];
    job_cards?: JobCard[];
    invoices?: Invoice[];
    created_at: string;
    updated_at: string;
}

export interface Booking {
    id: number;
    reference_number: string;
    customer_id: number;
    vehicle_id: number;
    assigned_to?: number;
    service_type: string;
    booking_date: string;
    booking_time: string;
    estimated_duration?: number;
    status: 'pending' | 'confirmed' | 'in_progress' | 'completed' | 'cancelled';
    notes?: string;
    customer_notes?: string;
    customer?: Customer;
    vehicle?: Vehicle;
    assigned_technician?: User;
    job_card?: JobCard;
    created_at: string;
    updated_at: string;
}

export interface MotTest {
    id: number;
    vehicle_id: number;
    job_card_id?: number;
    test_number?: string;
    test_date?: string;
    expiry_date?: string;
    result: 'booked' | 'passed' | 'failed' | 'advisory' | 'retest';
    mileage?: number;
    advisories?: string[];
    failures?: string[];
    notes?: string;
    certificate_path?: string;
    tester_name?: string;
    dvsa_data?: Record<string, any>;
    vehicle?: Vehicle;
    job_card?: JobCard;
    created_at: string;
    updated_at: string;
}

export interface JobCard {
    id: number;
    job_number: string;
    customer_id: number;
    vehicle_id: number;
    booking_id?: number;
    assigned_to?: number;
    status: 'pending' | 'in_progress' | 'awaiting_parts' | 'completed' | 'invoiced';
    priority: 'low' | 'normal' | 'high' | 'urgent';
    mileage_in?: number;
    mileage_out?: number;
    started_at?: string;
    completed_at?: string;
    technician_notes?: string;
    customer_notes?: string;
    labour_total: number;
    parts_total: number;
    vat_total: number;
    grand_total: number;
    customer?: Customer;
    vehicle?: Vehicle;
    assigned_technician?: User;
    booking?: Booking;
    labour_lines?: LabourLine[];
    part_lines?: PartLine[];
    invoice?: Invoice;
    created_at: string;
    updated_at: string;
}

export interface LabourLine {
    id: number;
    job_card_id: number;
    description: string;
    hours: number;
    rate: number;
    discount: number;
    vat_rate: number;
    total: number;
    vat_amount: number;
    completed: boolean;
}

export interface PartLine {
    id: number;
    job_card_id: number;
    part_id?: number;
    description: string;
    quantity: number;
    cost_price: number;
    unit_price: number;
    discount: number;
    vat_rate: number;
    total: number;
    vat_amount: number;
    status: string;
    part?: Part;
}

export interface Part {
    id: number;
    part_number: string;
    name: string;
    description?: string;
    category?: string;
    manufacturer?: string;
    supplier?: string;
    cost_price: number;
    retail_price: number;
    stock_quantity: number;
    min_stock_level: number;
    location?: string;
    sku?: string;
    barcode?: string;
    created_at: string;
    updated_at: string;
}

export interface Invoice {
    id: number;
    invoice_number: string;
    customer_id: number;
    vehicle_id?: number;
    job_card_id?: number;
    subtotal: number;
    vat_total: number;
    discount_total: number;
    grand_total: number;
    amount_paid: number;
    balance_due: number;
    status: 'draft' | 'sent' | 'paid' | 'partial' | 'overdue' | 'cancelled' | 'refunded';
    due_date?: string;
    paid_date?: string;
    notes?: string;
    terms?: string;
    customer?: Customer;
    vehicle?: Vehicle;
    job_card?: JobCard;
    items?: InvoiceItem[];
    payments?: Payment[];
    created_at: string;
    updated_at: string;
}

export interface InvoiceItem {
    id: number;
    invoice_id: number;
    type: 'labour' | 'part' | 'service' | 'other';
    description: string;
    quantity: number;
    unit_price: number;
    discount: number;
    vat_rate: number;
    total: number;
    vat_amount: number;
}

export interface Payment {
    id: number;
    invoice_id: number;
    customer_id: number;
    amount: number;
    method: 'card' | 'cash' | 'bank_transfer' | 'stripe' | 'cheque';
    reference?: string;
    stripe_payment_id?: string;
    status: 'pending' | 'completed' | 'failed' | 'refunded';
    notes?: string;
    paid_at: string;
    invoice?: Invoice;
    customer?: Customer;
    created_at: string;
    updated_at: string;
}

export interface Setting {
    id: number;
    key: string;
    value: string;
    group: string;
}

export interface GarageSettings {
    garage_name: string;
    address: string;
    city: string;
    postcode: string;
    phone: string;
    email: string;
    vat_number: string;
    vat_rate: number;
    logo_path?: string;
    default_labour_rate: number;
    invoice_prefix: string;
    invoice_terms: string;
    website?: string;
}

export interface DashboardStats {
    daily_revenue: number;
    monthly_revenue: number;
    mot_revenue: number;
    outstanding_invoices: number;
    outstanding_amount: number;
    jobs_in_progress: number;
    jobs_completed_today: number;
    bookings_today: number;
    bookings_this_week: number;
    low_stock_count: number;
    customers_total: number;
    vehicles_total: number;
    labour_revenue: number;
    parts_revenue: number;
    profit_margin: number;
}

export interface RevenueChart {
    labels: string[];
    revenue: number[];
    costs: number[];
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number;
    to: number;
    links: PaginationLink[];
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface FlashMessages {
    success?: string;
    error?: string;
    warning?: string;
    info?: string;
}

export type PageProps = {
    auth: {
        user: User;
    };
    flash: FlashMessages;
    garageSettings: GarageSettings;
    ziggy: {
        url: string;
        port: number | null;
        defaults: Record<string, any>;
        routes: Record<string, any>;
    };
};
