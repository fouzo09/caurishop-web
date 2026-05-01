--
-- PostgreSQL database dump
--

\restrict kEpPfsTGyIJMEQPnoGgQ72gX2sEH83Y9quyCcDpBOd4a6CPXpRBPfu8cgGEoqfU

-- Dumped from database version 16.11 (Debian 16.11-1.pgdg13+1)
-- Dumped by pg_dump version 16.11 (Debian 16.11-1.pgdg13+1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: activity_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activity_logs (
    id bigint NOT NULL,
    user_id bigint,
    action character varying(60) NOT NULL,
    description character varying(255) NOT NULL,
    ip_address character varying(45),
    user_agent text,
    metadata json,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.activity_logs OWNER TO postgres;

--
-- Name: activity_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activity_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_logs_id_seq OWNER TO postgres;

--
-- Name: activity_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activity_logs_id_seq OWNED BY public.activity_logs.id;


--
-- Name: app_notifications; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.app_notifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    type character varying(60) NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    icon character varying(60) DEFAULT 'fa-bell'::character varying NOT NULL,
    color character varying(20) DEFAULT 'primary'::character varying NOT NULL,
    link character varying(255),
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.app_notifications OWNER TO postgres;

--
-- Name: app_notifications_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.app_notifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.app_notifications_id_seq OWNER TO postgres;

--
-- Name: app_notifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.app_notifications_id_seq OWNED BY public.app_notifications.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: companies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.companies (
    id bigint NOT NULL,
    raison_sociale character varying(255) NOT NULL,
    registration_number character varying(255),
    email character varying(255),
    phone character varying(255),
    address character varying(255),
    city character varying(255),
    country character varying(255),
    credit_limit numeric(14,2),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    status character varying(255) DEFAULT 'approved'::character varying NOT NULL,
    gerant_nom character varying(100),
    gerant_prenom character varying(100),
    gerant_tel character varying(30),
    gerant_piece character varying(100),
    gerant_adresse character varying(255),
    date_creation date,
    nombre_employes character varying(50),
    doc_rccm character varying(255),
    doc_nif character varying(255),
    doc_statuts character varying(255),
    doc_cni character varying(255),
    doc_patente character varying(255),
    doc_domicile character varying(255),
    CONSTRAINT companies_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'approved'::character varying, 'rejected'::character varying])::text[])))
);


ALTER TABLE public.companies OWNER TO postgres;

--
-- Name: companies_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.companies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.companies_id_seq OWNER TO postgres;

--
-- Name: companies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.companies_id_seq OWNED BY public.companies.id;


--
-- Name: credit_plans; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.credit_plans (
    id bigint NOT NULL,
    order_id uuid NOT NULL,
    duration_months integer NOT NULL,
    installments_count integer NOT NULL,
    total_amount numeric(14,2) NOT NULL,
    outstanding_amount numeric(14,2) NOT NULL,
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    down_payment_amount numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    CONSTRAINT credit_plans_status_check CHECK (((status)::text = ANY ((ARRAY['active'::character varying, 'closed'::character varying])::text[])))
);


ALTER TABLE public.credit_plans OWNER TO postgres;

--
-- Name: credit_plans_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.credit_plans_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.credit_plans_id_seq OWNER TO postgres;

--
-- Name: credit_plans_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.credit_plans_id_seq OWNED BY public.credit_plans.id;


--
-- Name: customers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    type character varying(255) NOT NULL,
    company_id bigint,
    first_name character varying(255),
    last_name character varying(255),
    company_contact_name character varying(255),
    email character varying(255),
    phone character varying(255),
    address character varying(255),
    is_active boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    credit_limit numeric(14,2),
    user_id bigint,
    CONSTRAINT customers_type_check CHECK (((type)::text = ANY ((ARRAY['individual'::character varying, 'company'::character varying])::text[])))
);


ALTER TABLE public.customers OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.customers_id_seq OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: installments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.installments (
    id bigint NOT NULL,
    credit_plan_id bigint NOT NULL,
    installment_number integer NOT NULL,
    due_date date NOT NULL,
    amount_due numeric(14,2) NOT NULL,
    amount_paid numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT installments_status_check CHECK (((status)::text = ANY ((ARRAY['pending'::character varying, 'partial'::character varying, 'paid'::character varying, 'late'::character varying])::text[])))
);


ALTER TABLE public.installments OWNER TO postgres;

--
-- Name: installments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.installments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.installments_id_seq OWNER TO postgres;

--
-- Name: installments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.installments_id_seq OWNED BY public.installments.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_permissions (
    permission_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO postgres;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.model_has_roles (
    role_id bigint NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id bigint NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO postgres;

--
-- Name: order_items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.order_items (
    id bigint NOT NULL,
    order_id uuid NOT NULL,
    product_id bigint NOT NULL,
    variant_id bigint,
    quantity integer DEFAULT 1 NOT NULL,
    unit_price numeric(14,2) NOT NULL,
    line_total numeric(14,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.order_items OWNER TO postgres;

--
-- Name: order_items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.order_items_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.order_items_id_seq OWNER TO postgres;

--
-- Name: order_items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.order_items_id_seq OWNED BY public.order_items.id;


--
-- Name: orders; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.orders (
    id uuid NOT NULL,
    order_number character varying(255) NOT NULL,
    customer_id bigint NOT NULL,
    order_type character varying(255) NOT NULL,
    status character varying(255) DEFAULT 'draft'::character varying NOT NULL,
    total_amount numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    created_by bigint,
    confirmed_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    down_payment numeric(14,2) DEFAULT '0'::numeric NOT NULL,
    delivered_at timestamp(0) without time zone,
    credit_installments_count integer,
    CONSTRAINT orders_order_type_check CHECK (((order_type)::text = ANY ((ARRAY['cash'::character varying, 'credit'::character varying])::text[]))),
    CONSTRAINT orders_status_check CHECK (((status)::text = ANY ((ARRAY['pending_approval'::character varying, 'draft'::character varying, 'confirmed'::character varying, 'completed'::character varying, 'cancelled'::character varying])::text[])))
);


ALTER TABLE public.orders OWNER TO postgres;

--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: payment_allocations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payment_allocations (
    id bigint NOT NULL,
    payment_id uuid NOT NULL,
    installment_id bigint NOT NULL,
    amount_allocated numeric(14,2) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.payment_allocations OWNER TO postgres;

--
-- Name: payment_allocations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payment_allocations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payment_allocations_id_seq OWNER TO postgres;

--
-- Name: payment_allocations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payment_allocations_id_seq OWNED BY public.payment_allocations.id;


--
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    id uuid NOT NULL,
    customer_id bigint NOT NULL,
    order_id uuid,
    credit_plan_id bigint,
    amount numeric(14,2) NOT NULL,
    payment_date date DEFAULT '2026-01-26'::date NOT NULL,
    method character varying(255) DEFAULT 'cash'::character varying NOT NULL,
    reference character varying(255),
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT payments_method_check CHECK (((method)::text = ANY ((ARRAY['cash'::character varying, 'transfer'::character varying, 'mobile_money'::character varying, 'card'::character varying, 'other'::character varying])::text[])))
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: product_images; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_images (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    path character varying(255) NOT NULL,
    sort_order smallint DEFAULT '0'::smallint NOT NULL,
    is_primary boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.product_images OWNER TO postgres;

--
-- Name: product_images_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.product_images_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.product_images_id_seq OWNER TO postgres;

--
-- Name: product_images_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.product_images_id_seq OWNED BY public.product_images.id;


--
-- Name: product_variants; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.product_variants (
    id bigint NOT NULL,
    product_id bigint NOT NULL,
    sku character varying(255) NOT NULL,
    name character varying(255),
    attributes jsonb,
    price numeric(14,2) NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    credit_enabled boolean,
    credit_duration_months integer,
    credit_installments_count integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    stock_quantity integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.product_variants OWNER TO postgres;

--
-- Name: product_variants_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.product_variants_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.product_variants_id_seq OWNER TO postgres;

--
-- Name: product_variants_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.product_variants_id_seq OWNED BY public.product_variants.id;


--
-- Name: products; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.products (
    id bigint NOT NULL,
    type character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    sku character varying(255),
    price numeric(14,2),
    is_published boolean DEFAULT false NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    credit_enabled boolean DEFAULT false NOT NULL,
    credit_duration_months integer,
    credit_installments_count integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    slug character varying(255),
    stock_quantity integer DEFAULT 0 NOT NULL,
    low_stock_threshold integer DEFAULT 5 NOT NULL,
    is_service boolean DEFAULT false NOT NULL,
    provider character varying(255),
    CONSTRAINT products_type_check CHECK (((type)::text = ANY ((ARRAY['simple'::character varying, 'variable'::character varying])::text[])))
);


ALTER TABLE public.products OWNER TO postgres;

--
-- Name: products_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.products_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.products_id_seq OWNER TO postgres;

--
-- Name: products_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.products_id_seq OWNED BY public.products.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_has_permissions (
    permission_id bigint NOT NULL,
    role_id bigint NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO postgres;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: settings; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    key character varying(255) NOT NULL,
    value text,
    "group" character varying(50) DEFAULT 'general'::character varying NOT NULL,
    type character varying(20) DEFAULT 'text'::character varying NOT NULL,
    label character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.settings OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.settings_id_seq OWNER TO postgres;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: transactions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.transactions (
    id uuid NOT NULL,
    type character varying(255) NOT NULL,
    amount numeric(14,2) NOT NULL,
    order_id uuid,
    payment_id uuid,
    metadata jsonb,
    created_by bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    CONSTRAINT transactions_type_check CHECK (((type)::text = ANY ((ARRAY['payment'::character varying, 'adjustment'::character varying])::text[])))
);


ALTER TABLE public.transactions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_active boolean DEFAULT true NOT NULL,
    company_id bigint
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: activity_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_logs ALTER COLUMN id SET DEFAULT nextval('public.activity_logs_id_seq'::regclass);


--
-- Name: app_notifications id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_notifications ALTER COLUMN id SET DEFAULT nextval('public.app_notifications_id_seq'::regclass);


--
-- Name: companies id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies ALTER COLUMN id SET DEFAULT nextval('public.companies_id_seq'::regclass);


--
-- Name: credit_plans id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.credit_plans ALTER COLUMN id SET DEFAULT nextval('public.credit_plans_id_seq'::regclass);


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: installments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.installments ALTER COLUMN id SET DEFAULT nextval('public.installments_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: order_items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_items ALTER COLUMN id SET DEFAULT nextval('public.order_items_id_seq'::regclass);


--
-- Name: payment_allocations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_allocations ALTER COLUMN id SET DEFAULT nextval('public.payment_allocations_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: product_images id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_images ALTER COLUMN id SET DEFAULT nextval('public.product_images_id_seq'::regclass);


--
-- Name: product_variants id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_variants ALTER COLUMN id SET DEFAULT nextval('public.product_variants_id_seq'::regclass);


--
-- Name: products id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products ALTER COLUMN id SET DEFAULT nextval('public.products_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: activity_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activity_logs (id, user_id, action, description, ip_address, user_agent, metadata, created_at) FROM stdin;
1	1	settings_updated	Paramètres mis à jour — groupe : notifications	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 13:42:34
2	1	settings_updated	Paramètres mis à jour — groupe : notifications	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 13:42:39
3	1	logout	Déconnexion — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:10:50
4	6	login	Connexion réussie — Ibrahima Bah	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:10:56
5	6	logout	Déconnexion — Ibrahima Bah	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:11:29
6	5	login	Connexion réussie — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:11:43
7	5	logout	Déconnexion — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:20:37
8	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:20:42
9	1	logout	Déconnexion — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:21:09
10	5	login	Connexion réussie — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:21:19
11	5	logout	Déconnexion — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:22:01
12	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:22:36
13	1	logout	Déconnexion — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:23:07
14	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:25:18
15	1	logout	Déconnexion — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:27:06
16	9	login	Connexion réussie — Aissatou Barry	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:27:12
17	2	logout	Déconnexion — CAURISHOP Employee	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:32:24
18	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 14:32:42
19	1	logout	Déconnexion — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:39:44
20	5	login	Connexion réussie — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:39:58
21	9	logout	Déconnexion — Aissatou Barry	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:40:25
22	8	login	Connexion réussie — Oumar Diallo	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:40:30
23	8	order_approved	Commande CS-20260412-0001 approuvée par l'admin entreprise	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:41:19
24	5	logout	Déconnexion — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:41:35
25	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:41:48
26	8	logout	Déconnexion — Oumar Diallo	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:47:30
27	9	login	Connexion réussie — Aissatou Barry	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 15:47:46
28	9	logout	Déconnexion — Aissatou Barry	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 19:43:19
29	\N	login_failed	Tentative de connexion échouée — hlkkl@dbvl.com	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 19:43:34
30	\N	login_failed	Tentative de connexion échouée — hlkkl@dbvl.com	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 20:10:19
31	9	login	Connexion réussie — Aissatou Barry	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-12 20:30:04
32	5	login	Connexion réussie — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-13 21:20:16
33	6	login	Connexion réussie — Ibrahima Bah	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-14 17:29:05
34	6	logout	Déconnexion — Ibrahima Bah	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36	\N	2026-04-14 17:41:44
35	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	\N	2026-04-25 12:34:20
36	5	login	Connexion réussie — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	\N	2026-05-01 19:37:17
37	5	logout	Déconnexion — Fatoumata Camara	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	\N	2026-05-01 19:37:26
38	1	login	Connexion réussie — CAURISHOP Admin	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	\N	2026-05-01 19:37:53
\.


--
-- Data for Name: app_notifications; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.app_notifications (id, user_id, type, title, message, icon, color, link, read_at, created_at, updated_at) FROM stdin;
1	1	order_created	Nouvelle commande	La commande CS-20260412-0001 de Aissatou Barry vient d'être créée.	fa-shopping-cart	primary	http://127.0.0.1:8000/admin/orders/019d8258-b729-73e1-aaa8-379b131e1896	2026-04-12 15:48:27	2026-04-12 15:39:05	2026-04-12 15:48:27
3	1	payment_received	Paiement reçu	Paiement de 50 000 GNF reçu de Aissatou Barry.	fa-coins	success	http://127.0.0.1:8000/admin/payments	\N	2026-04-12 15:49:47	2026-04-12 15:49:47
2	1	payment_received	Paiement reçu	Paiement de 50 000 GNF reçu de Aissatou Barry.	fa-coins	success	http://127.0.0.1:8000/admin/payments	2026-04-12 15:50:13	2026-04-12 15:49:29	2026-04-12 15:50:13
4	1	order_created	Nouvelle commande	La commande CS-20260412-0002 de Aissatou Barry vient d'être créée.	fa-shopping-cart	primary	http://127.0.0.1:8000/admin/orders/019d8284-0345-7345-8dd2-2992f7ba38ba	\N	2026-04-12 16:26:22	2026-04-12 16:26:22
5	1	company_registration	Nouvelle demande d'inscription	DELTA a soumis une demande d'inscription et attend validation.	fa-building	warning	http://127.0.0.1:8000/admin/companies/7	\N	2026-05-01 20:09:28	2026-05-01 20:09:28
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
laravel-cache-spatie.permission.cache	a:3:{s:5:"alias";a:4:{s:1:"a";s:2:"id";s:1:"b";s:4:"name";s:1:"c";s:10:"guard_name";s:1:"r";s:5:"roles";}s:11:"permissions";a:33:{i:0;a:4:{s:1:"a";i:1;s:1:"b";s:10:"users.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:1;a:4:{s:1:"a";i:2;s:1:"b";s:12:"users.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:2;a:4:{s:1:"a";i:3;s:1:"b";s:10:"users.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:3;a:4:{s:1:"a";i:4;s:1:"b";s:12:"users.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:4;a:4:{s:1:"a";i:5;s:1:"b";s:13:"users.suspend";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:5;a:4:{s:1:"a";i:6;s:1:"b";s:14:"companies.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:6;a:4:{s:1:"a";i:7;s:1:"b";s:16:"companies.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:7;a:4:{s:1:"a";i:8;s:1:"b";s:14:"companies.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:8;a:4:{s:1:"a";i:9;s:1:"b";s:16:"companies.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:9;a:4:{s:1:"a";i:10;s:1:"b";s:18:"companies.activate";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:10;a:4:{s:1:"a";i:11;s:1:"b";s:14:"customers.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:11;a:4:{s:1:"a";i:12;s:1:"b";s:16:"customers.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:12;a:4:{s:1:"a";i:13;s:1:"b";s:14:"customers.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:13;a:4:{s:1:"a";i:14;s:1:"b";s:16:"customers.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:14;a:4:{s:1:"a";i:15;s:1:"b";s:18:"customers.activate";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:15;a:4:{s:1:"a";i:16;s:1:"b";s:13:"products.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:16;a:4:{s:1:"a";i:17;s:1:"b";s:15:"products.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:17;a:4:{s:1:"a";i:18;s:1:"b";s:13:"products.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:18;a:4:{s:1:"a";i:19;s:1:"b";s:15:"products.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:19;a:4:{s:1:"a";i:20;s:1:"b";s:16:"products.publish";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:20;a:4:{s:1:"a";i:21;s:1:"b";s:11:"orders.view";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:21;a:4:{s:1:"a";i:22;s:1:"b";s:13:"orders.create";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:22;a:4:{s:1:"a";i:23;s:1:"b";s:14:"orders.confirm";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:23;a:4:{s:1:"a";i:24;s:1:"b";s:14:"orders.deliver";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:24;a:4:{s:1:"a";i:25;s:1:"b";s:13:"orders.cancel";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:25;a:4:{s:1:"a";i:26;s:1:"b";s:13:"payments.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:26;a:4:{s:1:"a";i:27;s:1:"b";s:17:"installments.view";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:27;a:4:{s:1:"a";i:28;s:1:"b";s:16:"installments.pay";s:1:"c";s:3:"web";s:1:"r";a:2:{i:0;i:1;i:1;i:2;}}i:28;a:4:{s:1:"a";i:29;s:1:"b";s:10:"roles.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:29;a:4:{s:1:"a";i:30;s:1:"b";s:12:"roles.create";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:30;a:4:{s:1:"a";i:31;s:1:"b";s:10:"roles.edit";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:31;a:4:{s:1:"a";i:32;s:1:"b";s:12:"roles.delete";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}i:32;a:4:{s:1:"a";i:33;s:1:"b";s:16:"permissions.view";s:1:"c";s:3:"web";s:1:"r";a:1:{i:0;i:1;}}}s:5:"roles";a:2:{i:0;a:3:{s:1:"a";i:1;s:1:"b";s:5:"admin";s:1:"c";s:3:"web";}i:1;a:3:{s:1:"a";i:2;s:1:"b";s:8:"employee";s:1:"c";s:3:"web";}}}	1777752455
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: companies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.companies (id, raison_sociale, registration_number, email, phone, address, city, country, credit_limit, is_active, created_at, updated_at, status, gerant_nom, gerant_prenom, gerant_tel, gerant_piece, gerant_adresse, date_creation, nombre_employes, doc_rccm, doc_nif, doc_statuts, doc_cni, doc_patente, doc_domicile) FROM stdin;
1	CAURISHOP Entreprise A	RCCM-0001	contactA@company.test	+224 000 000 001	Conakry	Conakry	GN	5000000.00	t	2026-01-26 14:12:19	2026-01-26 14:12:19	approved	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
2	CAURISHOP Entreprise B	RCCM-0002	contactB@company.test	+224 000 000 002	Conakry	Conakry	GN	10000000.00	t	2026-01-26 14:12:19	2026-01-26 14:12:19	approved	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N
7	DELTA	RC0012HT	delta@delta.com	666998877	Lambanyi	Conakry	Guinée	\N	f	2026-05-01 20:09:28	2026-05-01 20:09:28	pending	DIALLO	Mafouz	666909091	Carte Nationale d'Identité (CNI)	Lambanyi	2026-05-31	1 – 5	companies/7/mgPsp2IPGpZZRV2l606wNj940pAEFPrn1E5ghl6N.pdf	companies/7/HvGZWog354iXRuyCCF92RW1ARwkO0kkxajGEbNbi.pdf	companies/7/nXcTP3wQYtt4LcjxKHtHBR2ZC2pBBqGxuMjd9SM7.pdf	companies/7/UdcaN0jv3kR7DgSpnXog6fefZqD0ikxlFTZWP4lL.pdf	companies/7/6BdyagmaRRRVZhluvMoCFVjfH9f4p9Mt903l1T9e.pdf	companies/7/PvAbPdc0lhWKFVvxFYEsPoNCJ1Gf1OZSidB1aNPA.pdf
\.


--
-- Data for Name: credit_plans; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.credit_plans (id, order_id, duration_months, installments_count, total_amount, outstanding_amount, status, created_at, updated_at, down_payment_amount) FROM stdin;
2	019d405f-df3a-7121-be1a-bf4554a944b2	6	5	450000.00	450000.00	active	2026-03-30 20:12:09	2026-03-30 20:12:09	200000.00
3	019d4088-578e-7388-a28a-0d6e9b777b71	5	5	500000.00	500000.00	active	2026-03-30 20:56:12	2026-03-30 20:56:12	150000.00
1	019d405d-d4ce-7172-a3f7-eac758e005d7	10	12	649950.00	595787.50	active	2026-03-30 20:10:05	2026-03-30 21:05:22	50.00
4	019d8258-b729-73e1-aaa8-379b131e1896	12	12	600000.00	500000.00	active	2026-04-12 15:48:33	2026-04-12 15:49:47	50000.00
\.


--
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.customers (id, type, company_id, first_name, last_name, company_contact_name, email, phone, address, is_active, created_at, updated_at, credit_limit, user_id) FROM stdin;
1	individual	\N	Mamadou	Diallo	\N	mamadou@client.test	+224 600 000 000	Conakry	t	2026-01-26 14:12:19	2026-01-26 14:12:19	\N	\N
2	company	1	\N	\N	Responsable Achats	achats@companyA.test	+224 610 000 000	Conakry	t	2026-01-26 14:12:19	2026-01-26 14:12:19	\N	\N
3	company	2	Aissatou	Barry	\N	aissatou@entreprise-b.test	\N	\N	t	2026-04-12 15:38:28	2026-04-12 15:38:28	\N	9
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: installments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.installments (id, credit_plan_id, installment_number, due_date, amount_due, amount_paid, status, created_at, updated_at) FROM stdin;
2	1	2	2026-05-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
3	1	3	2026-06-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
4	1	4	2026-07-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
5	1	5	2026-08-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
6	1	6	2026-09-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
7	1	7	2026-10-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
8	1	8	2026-11-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
9	1	9	2026-12-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
10	1	10	2027-01-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
11	1	11	2027-03-02	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
12	1	12	2027-03-30	54162.50	0.00	pending	2026-03-30 20:10:05	2026-03-30 20:10:05
13	2	1	2026-04-30	90000.00	0.00	pending	2026-03-30 20:12:09	2026-03-30 20:12:09
14	2	2	2026-05-30	90000.00	0.00	pending	2026-03-30 20:12:09	2026-03-30 20:12:09
15	2	3	2026-06-30	90000.00	0.00	pending	2026-03-30 20:12:09	2026-03-30 20:12:09
16	2	4	2026-07-30	90000.00	0.00	pending	2026-03-30 20:12:09	2026-03-30 20:12:09
17	2	5	2026-08-30	90000.00	0.00	pending	2026-03-30 20:12:09	2026-03-30 20:12:09
18	3	1	2026-04-30	100000.00	0.00	pending	2026-03-30 20:56:12	2026-03-30 20:56:12
19	3	2	2026-05-30	100000.00	0.00	pending	2026-03-30 20:56:12	2026-03-30 20:56:12
20	3	3	2026-06-30	100000.00	0.00	pending	2026-03-30 20:56:12	2026-03-30 20:56:12
21	3	4	2026-07-30	100000.00	0.00	pending	2026-03-30 20:56:12	2026-03-30 20:56:12
22	3	5	2026-08-30	100000.00	0.00	pending	2026-03-30 20:56:12	2026-03-30 20:56:12
1	1	1	2026-04-30	54162.50	54162.50	paid	2026-03-30 20:10:05	2026-03-30 21:05:22
25	4	3	2026-07-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
26	4	4	2026-08-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
27	4	5	2026-09-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
28	4	6	2026-10-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
29	4	7	2026-11-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
30	4	8	2026-12-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
31	4	9	2027-01-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
32	4	10	2027-02-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
33	4	11	2027-03-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
34	4	12	2027-04-12	50000.00	0.00	pending	2026-04-12 15:48:33	2026-04-12 15:48:33
23	4	1	2026-05-12	50000.00	50000.00	paid	2026-04-12 15:48:33	2026-04-12 15:49:29
24	4	2	2026-06-12	50000.00	50000.00	paid	2026-04-12 15:48:33	2026-04-12 15:49:47
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_01_15_103311_create_permission_tables	1
5	2026_01_15_103327_create_companies_table	1
6	2026_01_15_103335_create_customers_table	1
7	2026_01_15_103343_create_products_table	1
8	2026_01_15_103351_create_product_variants_table	1
9	2026_01_15_103359_create_orders_table	1
10	2026_01_15_103408_create_order_items_table	1
11	2026_01_15_103416_create_credit_plans_table	1
12	2026_01_15_103424_create_installments_table	1
13	2026_01_15_103432_create_payments_table	1
14	2026_01_15_103440_create_payment_allocations_table	1
15	2026_01_15_103447_create_transactions_table	1
16	2026_01_15_103905_add_is_active_to_users_table	1
17	2026_03_29_000001_add_missing_columns_to_products_table	2
18	2026_03_29_000002_add_stock_quantity_to_product_variants_table	2
19	2026_03_29_000003_add_credit_limit_to_customers_table	3
20	2026_03_29_000004_add_down_payment_and_delivered_at_to_orders_table	3
21	2026_03_29_000005_add_down_payment_amount_to_credit_plans_table	3
22	2026_03_29_000006_add_credit_terms_to_orders_table	4
23	2026_03_30_000001_drop_credit_duration_months_from_orders_table	5
24	2026_04_12_133222_create_app_notifications_table	6
25	2026_04_12_133222_create_settings_table	6
26	2026_04_12_133223_create_activity_logs_table	6
27	2026_04_12_135344_add_company_id_to_users_table	7
28	2026_04_12_135344_add_pending_approval_to_orders_status	7
29	2026_04_12_135344_add_user_id_to_customers_table	7
30	2026_04_12_155454_create_product_images_table	8
31	2026_04_12_161921_add_service_fields_to_products_table	9
32	2026_05_01_000000_enrich_companies_table	10
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
2	App\\Models\\User	2
1	App\\Models\\User	1
3	App\\Models\\User	5
4	App\\Models\\User	6
4	App\\Models\\User	7
3	App\\Models\\User	8
4	App\\Models\\User	9
\.


--
-- Data for Name: order_items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.order_items (id, order_id, product_id, variant_id, quantity, unit_price, line_total, created_at, updated_at) FROM stdin;
1	019d405b-f7b4-72bb-9d05-2094842c2eab	1	\N	1	12000000.00	12000000.00	2026-03-30 20:07:42	2026-03-30 20:07:42
2	019d405d-d4ce-7172-a3f7-eac758e005d7	2	\N	1	650000.00	650000.00	2026-03-30 20:09:44	2026-03-30 20:09:44
3	019d405f-df3a-7121-be1a-bf4554a944b2	2	\N	1	650000.00	650000.00	2026-03-30 20:11:58	2026-03-30 20:11:58
4	019d4088-578e-7388-a28a-0d6e9b777b71	2	\N	1	650000.00	650000.00	2026-03-30 20:56:10	2026-03-30 20:56:10
5	019d8258-b729-73e1-aaa8-379b131e1896	2	\N	1	650000.00	650000.00	2026-04-12 15:39:05	2026-04-12 15:39:05
6	019d8284-0345-7345-8dd2-2992f7ba38ba	21	\N	1	0.00	0.00	2026-04-12 16:26:22	2026-04-12 16:26:22
7	019d8284-0345-7345-8dd2-2992f7ba38ba	20	\N	1	180000.00	180000.00	2026-04-12 16:26:22	2026-04-12 16:26:22
\.


--
-- Data for Name: orders; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.orders (id, order_number, customer_id, order_type, status, total_amount, created_by, confirmed_at, created_at, updated_at, down_payment, delivered_at, credit_installments_count) FROM stdin;
019d405b-f7b4-72bb-9d05-2094842c2eab	CS-20260330-0001	1	cash	cancelled	12000000.00	1	\N	2026-03-30 20:07:42	2026-03-30 20:07:53	7000000.00	\N	\N
019d405d-d4ce-7172-a3f7-eac758e005d7	CS-20260330-0002	2	credit	completed	650000.00	1	2026-03-30 20:10:05	2026-03-30 20:09:44	2026-03-30 20:10:42	50.00	2026-03-30 20:10:42	12
019d405f-df3a-7121-be1a-bf4554a944b2	CS-20260330-0003	2	credit	completed	650000.00	1	2026-03-30 20:12:09	2026-03-30 20:11:58	2026-03-30 20:12:28	200000.00	2026-03-30 20:12:28	5
019d4088-578e-7388-a28a-0d6e9b777b71	CS-20260330-0004	2	credit	completed	650000.00	1	2026-03-30 20:56:12	2026-03-30 20:56:10	2026-03-30 20:56:18	150000.00	2026-03-30 20:56:18	5
019d8258-b729-73e1-aaa8-379b131e1896	CS-20260412-0001	3	credit	completed	650000.00	9	2026-04-12 15:48:33	2026-04-12 15:39:05	2026-04-12 15:48:58	50000.00	2026-04-12 15:48:58	12
019d8284-0345-7345-8dd2-2992f7ba38ba	CS-20260412-0002	3	credit	pending_approval	180000.00	9	\N	2026-04-12 16:26:22	2026-04-12 16:26:22	80000.00	\N	3
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: payment_allocations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payment_allocations (id, payment_id, installment_id, amount_allocated, created_at, updated_at) FROM stdin;
1	019d4090-c599-7321-a386-863a82f55ade	1	54162.50	2026-03-30 21:05:22	2026-03-30 21:05:22
2	019d8262-3bb1-739d-8402-2877a8266da9	23	50000.00	2026-04-12 15:49:29	2026-04-12 15:49:29
3	019d8262-81f8-71ac-bd0b-72ab8f3b2480	24	50000.00	2026-04-12 15:49:47	2026-04-12 15:49:47
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (id, customer_id, order_id, credit_plan_id, amount, payment_date, method, reference, created_by, created_at, updated_at) FROM stdin;
019d4090-c599-7321-a386-863a82f55ade	2	019d405d-d4ce-7172-a3f7-eac758e005d7	1	54162.50	2026-03-30	transfer	\N	1	2026-03-30 21:05:22	2026-03-30 21:05:22
019d8262-3bb1-739d-8402-2877a8266da9	3	019d8258-b729-73e1-aaa8-379b131e1896	4	50000.00	2026-04-12	transfer	\N	1	2026-04-12 15:49:29	2026-04-12 15:49:29
019d8262-81f8-71ac-bd0b-72ab8f3b2480	3	019d8258-b729-73e1-aaa8-379b131e1896	4	50000.00	2026-04-12	cash	\N	1	2026-04-12 15:49:47	2026-04-12 15:49:47
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
1	users.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
2	users.create	web	2026-04-12 13:03:31	2026-04-12 13:03:31
3	users.edit	web	2026-04-12 13:03:31	2026-04-12 13:03:31
4	users.delete	web	2026-04-12 13:03:31	2026-04-12 13:03:31
5	users.suspend	web	2026-04-12 13:03:31	2026-04-12 13:03:31
6	companies.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
7	companies.create	web	2026-04-12 13:03:31	2026-04-12 13:03:31
8	companies.edit	web	2026-04-12 13:03:31	2026-04-12 13:03:31
9	companies.delete	web	2026-04-12 13:03:31	2026-04-12 13:03:31
10	companies.activate	web	2026-04-12 13:03:31	2026-04-12 13:03:31
11	customers.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
12	customers.create	web	2026-04-12 13:03:31	2026-04-12 13:03:31
13	customers.edit	web	2026-04-12 13:03:31	2026-04-12 13:03:31
14	customers.delete	web	2026-04-12 13:03:31	2026-04-12 13:03:31
15	customers.activate	web	2026-04-12 13:03:31	2026-04-12 13:03:31
16	products.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
17	products.create	web	2026-04-12 13:03:31	2026-04-12 13:03:31
18	products.edit	web	2026-04-12 13:03:31	2026-04-12 13:03:31
19	products.delete	web	2026-04-12 13:03:31	2026-04-12 13:03:31
20	products.publish	web	2026-04-12 13:03:31	2026-04-12 13:03:31
21	orders.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
22	orders.create	web	2026-04-12 13:03:31	2026-04-12 13:03:31
23	orders.confirm	web	2026-04-12 13:03:31	2026-04-12 13:03:31
24	orders.deliver	web	2026-04-12 13:03:31	2026-04-12 13:03:31
25	orders.cancel	web	2026-04-12 13:03:31	2026-04-12 13:03:31
26	payments.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
27	installments.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
28	installments.pay	web	2026-04-12 13:03:31	2026-04-12 13:03:31
29	roles.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
30	roles.create	web	2026-04-12 13:03:31	2026-04-12 13:03:31
31	roles.edit	web	2026-04-12 13:03:31	2026-04-12 13:03:31
32	roles.delete	web	2026-04-12 13:03:31	2026-04-12 13:03:31
33	permissions.view	web	2026-04-12 13:03:31	2026-04-12 13:03:31
\.


--
-- Data for Name: product_images; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_images (id, product_id, path, sort_order, is_primary, created_at, updated_at) FROM stdin;
12	1	products/1/img_0.jpg	0	t	2026-04-12 16:01:35	2026-04-12 16:01:35
13	1	products/1/img_1.jpg	1	f	2026-04-12 16:01:35	2026-04-12 16:01:35
14	1	products/1/img_2.jpg	2	f	2026-04-12 16:01:35	2026-04-12 16:01:35
15	2	products/2/img_0.jpg	0	t	2026-04-12 16:01:36	2026-04-12 16:01:36
16	2	products/2/img_1.jpg	1	f	2026-04-12 16:01:36	2026-04-12 16:01:36
17	3	products/3/img_0.jpg	0	t	2026-04-12 16:01:36	2026-04-12 16:01:36
18	3	products/3/img_1.jpg	1	f	2026-04-12 16:01:36	2026-04-12 16:01:36
19	3	products/3/img_2.jpg	2	f	2026-04-12 16:01:36	2026-04-12 16:01:36
20	4	products/4/img_0.jpg	0	t	2026-04-12 16:01:36	2026-04-12 16:01:36
21	4	products/4/img_1.jpg	1	f	2026-04-12 16:01:37	2026-04-12 16:01:37
22	4	products/4/img_2.jpg	2	f	2026-04-12 16:01:37	2026-04-12 16:01:37
23	5	products/5/img_0.jpg	0	t	2026-04-12 16:04:42	2026-04-12 16:04:42
24	5	products/5/img_1.jpg	1	f	2026-04-12 16:04:42	2026-04-12 16:04:42
25	6	products/6/img_0.jpg	0	t	2026-04-12 16:04:42	2026-04-12 16:04:42
26	7	products/7/img_0.jpg	0	t	2026-04-12 16:04:44	2026-04-12 16:04:44
27	7	products/7/img_1.jpg	1	f	2026-04-12 16:04:44	2026-04-12 16:04:44
28	7	products/7/img_2.jpg	2	f	2026-04-12 16:04:45	2026-04-12 16:04:45
29	8	products/8/img_0.jpg	0	t	2026-04-12 16:04:45	2026-04-12 16:04:45
30	8	products/8/img_1.jpg	1	f	2026-04-12 16:04:46	2026-04-12 16:04:46
31	9	products/9/img_0.jpg	0	t	2026-04-12 16:04:46	2026-04-12 16:04:46
32	9	products/9/img_1.jpg	1	f	2026-04-12 16:04:47	2026-04-12 16:04:47
33	10	products/10/img_0.jpg	0	t	2026-04-12 16:04:47	2026-04-12 16:04:47
34	10	products/10/img_1.jpg	1	f	2026-04-12 16:04:47	2026-04-12 16:04:47
35	10	products/10/img_2.jpg	2	f	2026-04-12 16:04:47	2026-04-12 16:04:47
36	11	products/11/img_0.jpg	0	t	2026-04-12 16:04:47	2026-04-12 16:04:47
37	11	products/11/img_1.jpg	1	f	2026-04-12 16:04:48	2026-04-12 16:04:48
38	12	products/12/img_0.jpg	0	t	2026-04-12 16:04:48	2026-04-12 16:04:48
39	12	products/12/img_1.jpg	1	f	2026-04-12 16:04:48	2026-04-12 16:04:48
40	13	products/13/img_0.jpg	0	t	2026-04-12 16:04:48	2026-04-12 16:04:48
41	13	products/13/img_1.jpg	1	f	2026-04-12 16:04:48	2026-04-12 16:04:48
42	14	products/14/img_1.jpg	1	f	2026-04-12 16:04:49	2026-04-12 16:04:49
43	15	products/15/img_0.jpg	0	t	2026-04-12 16:04:49	2026-04-12 16:04:49
44	15	products/15/img_1.jpg	1	f	2026-04-12 16:04:49	2026-04-12 16:04:49
45	15	products/15/img_2.jpg	2	f	2026-04-12 16:04:50	2026-04-12 16:04:50
46	16	products/16/img_0.jpg	0	t	2026-04-12 16:04:50	2026-04-12 16:04:50
47	16	products/16/img_1.jpg	1	f	2026-04-12 16:04:50	2026-04-12 16:04:50
48	17	products/17/img_0.jpg	0	t	2026-04-12 16:04:50	2026-04-12 16:04:50
49	17	products/17/img_1.jpg	1	f	2026-04-12 16:04:51	2026-04-12 16:04:51
50	18	products/18/img_0.jpg	0	t	2026-04-12 16:04:51	2026-04-12 16:04:51
51	18	products/18/img_1.jpg	1	f	2026-04-12 16:04:51	2026-04-12 16:04:51
52	19	products/19/img_0.jpg	0	t	2026-04-12 16:04:51	2026-04-12 16:04:51
53	19	products/19/img_1.jpg	1	f	2026-04-12 16:04:52	2026-04-12 16:04:52
54	19	products/19/img_fix.jpg	2	f	2026-04-12 16:05:11	2026-04-12 16:05:11
55	6	products/6/img_fix.jpg	1	f	2026-04-12 16:05:24	2026-04-12 16:05:24
56	14	products/14/img_fix.jpg	2	f	2026-04-12 16:05:24	2026-04-12 16:05:24
57	20	products/20/img_0.jpg	0	t	2026-04-12 16:23:46	2026-04-12 16:23:46
58	20	products/20/img_1.jpg	1	f	2026-04-12 16:23:47	2026-04-12 16:23:47
59	21	products/21/img_0.jpg	0	t	2026-04-12 16:23:47	2026-04-12 16:23:47
60	21	products/21/img_1.jpg	1	f	2026-04-12 16:23:47	2026-04-12 16:23:47
61	22	products/22/img_0.jpg	0	t	2026-04-12 16:23:47	2026-04-12 16:23:47
62	22	products/22/img_1.jpg	1	f	2026-04-12 16:23:47	2026-04-12 16:23:47
63	23	products/23/img_0.jpg	0	t	2026-04-12 16:23:47	2026-04-12 16:23:47
64	23	products/23/img_1.jpg	1	f	2026-04-12 16:23:48	2026-04-12 16:23:48
65	24	products/24/img_0.jpg	0	t	2026-04-12 16:23:48	2026-04-12 16:23:48
66	24	products/24/img_1.jpg	1	f	2026-04-12 16:23:48	2026-04-12 16:23:48
67	25	products/25/img_0.jpg	0	t	2026-04-12 16:23:48	2026-04-12 16:23:48
68	25	products/25/img_1.jpg	1	f	2026-04-12 16:23:48	2026-04-12 16:23:48
69	26	products/26/img_0.jpg	0	t	2026-04-12 16:23:49	2026-04-12 16:23:49
70	26	products/26/img_1.jpg	1	f	2026-04-12 16:23:50	2026-04-12 16:23:50
71	27	products/27/img_0.jpg	0	t	2026-04-12 16:23:50	2026-04-12 16:23:50
72	27	products/27/img_1.jpg	1	f	2026-04-12 16:23:50	2026-04-12 16:23:50
73	28	products/28/img_0.jpg	0	t	2026-04-12 16:23:50	2026-04-12 16:23:50
74	28	products/28/img_1.jpg	1	f	2026-04-12 16:23:50	2026-04-12 16:23:50
75	29	products/29/img_0.jpg	0	t	2026-04-12 16:23:51	2026-04-12 16:23:51
76	29	products/29/img_1.jpg	1	f	2026-04-12 16:23:51	2026-04-12 16:23:51
77	30	products/30/img_0.jpg	0	t	2026-04-12 16:23:51	2026-04-12 16:23:51
78	30	products/30/img_1.jpg	1	f	2026-04-12 16:23:51	2026-04-12 16:23:51
79	31	products/31/img_0.jpg	0	t	2026-04-12 16:23:51	2026-04-12 16:23:51
80	31	products/31/img_1.jpg	1	f	2026-04-12 16:23:52	2026-04-12 16:23:52
81	32	products/32/img_0.jpg	0	t	2026-04-12 16:23:52	2026-04-12 16:23:52
82	32	products/32/img_1.jpg	1	f	2026-04-12 16:23:52	2026-04-12 16:23:52
83	33	products/33/img_0.jpg	0	t	2026-04-12 16:23:52	2026-04-12 16:23:52
84	33	products/33/img_1.jpg	1	f	2026-04-12 16:23:52	2026-04-12 16:23:52
85	34	products/34/img_0.jpg	0	t	2026-04-12 16:23:52	2026-04-12 16:23:52
86	34	products/34/img_1.jpg	1	f	2026-04-12 16:23:53	2026-04-12 16:23:53
87	35	products/35/img_0.jpg	0	t	2026-04-12 16:23:53	2026-04-12 16:23:53
88	35	products/35/img_1.jpg	1	f	2026-04-12 16:23:53	2026-04-12 16:23:53
89	36	products/36/img_0.jpg	0	t	2026-04-12 16:23:53	2026-04-12 16:23:53
90	36	products/36/img_1.jpg	1	f	2026-04-12 16:23:53	2026-04-12 16:23:53
91	37	products/37/img_0.jpg	0	t	2026-04-12 16:23:53	2026-04-12 16:23:53
92	37	products/37/img_1.jpg	1	f	2026-04-12 16:23:54	2026-04-12 16:23:54
\.


--
-- Data for Name: product_variants; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.product_variants (id, product_id, sku, name, attributes, price, is_active, credit_enabled, credit_duration_months, credit_installments_count, created_at, updated_at, stock_quantity) FROM stdin;
1	3	TSHIRT-BLK-M	Noir - M	{"size": "M", "color": "black"}	120000.00	t	\N	\N	\N	2026-01-26 14:12:19	2026-01-26 14:12:19	0
2	3	TSHIRT-BLK-L	Noir - L	{"size": "L", "color": "black"}	120000.00	t	\N	\N	\N	2026-01-26 14:12:19	2026-01-26 14:12:19	0
3	3	TSHIRT-WHT-M	Blanc - M	{"size": "M", "color": "white"}	110000.00	t	\N	\N	\N	2026-01-26 14:12:19	2026-01-26 14:12:19	0
4	7	IPAD-PRO-GS-256	Gris Sidéral 256 Go	{"couleur": "Gris Sidéral", "stockage": "256 Go"}	9500000.00	t	\N	\N	\N	2026-04-12 16:04:43	2026-04-12 16:04:43	10
5	7	IPAD-PRO-AR-256	Argent 256 Go	{"couleur": "Argent", "stockage": "256 Go"}	9500000.00	t	\N	\N	\N	2026-04-12 16:04:43	2026-04-12 16:04:43	10
6	7	IPAD-PRO-GS-512	Gris Sidéral 512 Go	{"couleur": "Gris Sidéral", "stockage": "512 Go"}	11500000.00	t	\N	\N	\N	2026-04-12 16:04:43	2026-04-12 16:04:43	8
7	7	IPAD-PRO-AR-1T	Argent 1 To	{"couleur": "Argent", "stockage": "1 To"}	14500000.00	t	\N	\N	\N	2026-04-12 16:04:43	2026-04-12 16:04:43	5
8	8	KB-RGB-BK-R	Noir – Switches Rouges	{"couleur": "Noir", "switches": "Rouge"}	890000.00	t	\N	\N	\N	2026-04-12 16:04:45	2026-04-12 16:04:45	20
9	8	KB-RGB-WH-R	Blanc – Switches Rouges	{"couleur": "Blanc", "switches": "Rouge"}	890000.00	t	\N	\N	\N	2026-04-12 16:04:45	2026-04-12 16:04:45	15
10	8	KB-RGB-BK-B	Noir – Switches Bleus	{"couleur": "Noir", "switches": "Bleu"}	950000.00	t	\N	\N	\N	2026-04-12 16:04:45	2026-04-12 16:04:45	12
11	9	SSD-EXT-500	500 Go	{"capacité": "500 Go"}	680000.00	t	\N	\N	\N	2026-04-12 16:04:46	2026-04-12 16:04:46	30
12	9	SSD-EXT-1T	1 To	{"capacité": "1 To"}	1100000.00	t	\N	\N	\N	2026-04-12 16:04:46	2026-04-12 16:04:46	25
13	9	SSD-EXT-2T	2 To	{"capacité": "2 To"}	1900000.00	t	\N	\N	\N	2026-04-12 16:04:46	2026-04-12 16:04:46	15
14	10	NIKE-AM-WH-40	Blanc – T40	{"couleur": "Blanc", "pointure": "40"}	1300000.00	t	\N	\N	\N	2026-04-12 16:04:47	2026-04-12 16:04:47	8
15	10	NIKE-AM-WH-41	Blanc – T41	{"couleur": "Blanc", "pointure": "41"}	1300000.00	t	\N	\N	\N	2026-04-12 16:04:47	2026-04-12 16:04:47	10
16	10	NIKE-AM-WH-42	Blanc – T42	{"couleur": "Blanc", "pointure": "42"}	1300000.00	t	\N	\N	\N	2026-04-12 16:04:47	2026-04-12 16:04:47	10
17	10	NIKE-AM-BK-41	Noir – T41	{"couleur": "Noir", "pointure": "41"}	1350000.00	t	\N	\N	\N	2026-04-12 16:04:47	2026-04-12 16:04:47	6
18	10	NIKE-AM-BK-42	Noir – T42	{"couleur": "Noir", "pointure": "42"}	1350000.00	t	\N	\N	\N	2026-04-12 16:04:47	2026-04-12 16:04:47	7
19	12	BAG-LEATHER-BK	Noir	{"couleur": "Noir"}	1200000.00	t	\N	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	12
20	12	BAG-LEATHER-BR	Marron	{"couleur": "Marron"}	1200000.00	t	\N	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	10
21	12	BAG-LEATHER-BE	Beige	{"couleur": "Beige"}	1250000.00	t	\N	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	8
22	14	PERF-PRES-50	50 ml	{"contenance": "50 ml"}	520000.00	t	\N	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	20
23	14	PERF-PRES-100	100 ml	{"contenance": "100 ml"}	870000.00	t	\N	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	15
24	14	PERF-PRES-200	200 ml	{"contenance": "200 ml"}	1400000.00	t	\N	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	8
25	15	CAFE-ARB-250G	250 g – Grains	{"forme": "Grains", "poids": "250 g"}	180000.00	t	\N	\N	\N	2026-04-12 16:04:49	2026-04-12 16:04:49	50
26	15	CAFE-ARB-500G	500 g – Grains	{"forme": "Grains", "poids": "500 g"}	320000.00	t	\N	\N	\N	2026-04-12 16:04:49	2026-04-12 16:04:49	40
27	15	CAFE-ARB-250M	250 g – Moulu	{"forme": "Moulu", "poids": "250 g"}	185000.00	t	\N	\N	\N	2026-04-12 16:04:49	2026-04-12 16:04:49	40
28	15	CAFE-ARB-1KG	1 kg – Grains	{"forme": "Grains", "poids": "1 kg"}	580000.00	t	\N	\N	\N	2026-04-12 16:04:49	2026-04-12 16:04:49	25
29	17	MIEL-FL-250	Miel de Fleurs 250 g	{"poids": "250 g", "variété": "Fleurs"}	130000.00	t	\N	\N	\N	2026-04-12 16:04:50	2026-04-12 16:04:50	45
30	17	MIEL-FL-500	Miel de Fleurs 500 g	{"poids": "500 g", "variété": "Fleurs"}	235000.00	t	\N	\N	\N	2026-04-12 16:04:50	2026-04-12 16:04:50	30
31	17	MIEL-AC-250	Miel d'Acacia 250 g	{"poids": "250 g", "variété": "Acacia"}	155000.00	t	\N	\N	\N	2026-04-12 16:04:50	2026-04-12 16:04:50	35
32	17	MIEL-AC-500	Miel d'Acacia 500 g	{"poids": "500 g", "variété": "Acacia"}	285000.00	t	\N	\N	\N	2026-04-12 16:04:50	2026-04-12 16:04:50	25
33	19	CHOC-NOIR-100	Noir 75 % – 100 g	{"type": "Noir 75 %", "poids": "100 g"}	88000.00	t	\N	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	60
34	19	CHOC-LAIT-100	Lait – 100 g	{"type": "Lait", "poids": "100 g"}	82000.00	t	\N	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	60
35	19	CHOC-BLANC-100	Blanc – 100 g	{"type": "Blanc", "poids": "100 g"}	80000.00	t	\N	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	50
36	19	CHOC-NOIR-250	Noir 75 % – 250 g	{"type": "Noir 75 %", "poids": "250 g"}	195000.00	t	\N	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	40
37	19	CHOC-LAIT-250	Lait – 250 g	{"type": "Lait", "poids": "250 g"}	182000.00	t	\N	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	40
38	21	SVC-MANI-S	Manucure simple	{"prestation": "Manucure"}	80000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
39	21	SVC-PEDI-S	Pédicure simple	{"prestation": "Pédicure"}	90000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
40	21	SVC-MANI-P	Manucure + Pédicure	{"prestation": "Manucure + Pédicure"}	150000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
41	22	SVC-EPIL-DJ	Demi-jambes	{"zone": "Demi-jambes"}	70000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
42	22	SVC-EPIL-JE	Jambes entières	{"zone": "Jambes entières"}	110000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
43	22	SVC-EPIL-AX	Aisselles	{"zone": "Aisselles"}	45000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
44	22	SVC-EPIL-CC	Corps complet	{"zone": "Corps complet"}	220000.00	t	\N	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	0
45	24	SVC-MAKE-D	Maquillage jour	{"occasion": "Jour"}	100000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
46	24	SVC-MAKE-S	Maquillage soirée	{"occasion": "Soirée"}	160000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
47	24	SVC-MAKE-M	Maquillage mariée	{"occasion": "Mariage"}	350000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
48	25	SVC-COACH-1	1 séance	{"formule": "1 séance"}	150000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
49	25	SVC-COACH-5	5 séances	{"formule": "5 séances"}	650000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
50	25	SVC-COACH-10	10 séances	{"formule": "10 séances"}	1200000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
51	25	SVC-COACH-1M	1 mois	{"formule": "1 mois illimité"}	1800000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
52	26	SVC-SWIM-D1	Débutant – 1 séance	{"niveau": "Débutant", "formule": "1 séance"}	80000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
53	26	SVC-SWIM-C1	Confirmé – 1 séance	{"niveau": "Confirmé", "formule": "1 séance"}	90000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
54	26	SVC-SWIM-D8	Débutant – 8 séances	{"niveau": "Débutant", "formule": "8 séances"}	550000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
55	26	SVC-SWIM-C8	Confirmé – 8 séances	{"niveau": "Confirmé", "formule": "8 séances"}	620000.00	t	\N	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	0
56	27	SVC-YOGA-1	1 cours	{"formule": "1 cours"}	70000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
57	27	SVC-YOGA-5	5 cours	{"formule": "5 cours"}	300000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
58	27	SVC-YOGA-M	Abonnement mensuel	{"formule": "Mensuel illimité"}	500000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
59	28	SVC-MART-E1	Enfant – 1 mois	{"durée": "1 mois", "tranche": "Enfant"}	250000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
60	28	SVC-MART-A1	Adulte – 1 mois	{"durée": "1 mois", "tranche": "Adulte"}	350000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
61	28	SVC-MART-A3	Adulte – 3 mois	{"durée": "3 mois", "tranche": "Adulte"}	900000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
62	29	SVC-PILATES-1	1 cours	{"formule": "1 cours"}	90000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
63	29	SVC-PILATES-8	8 cours	{"formule": "8 cours"}	640000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
64	29	SVC-PILATES-M	Abonnement 1 mois	{"formule": "1 mois illimité"}	750000.00	t	\N	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	0
65	30	GYM-ABO-1M	1 mois	{"durée": "1 mois"}	400000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
66	30	GYM-ABO-3M	3 mois	{"durée": "3 mois"}	1050000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
67	30	GYM-ABO-6M	6 mois	{"durée": "6 mois"}	1800000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
68	30	GYM-ABO-1Y	1 an	{"durée": "1 an"}	3200000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
69	31	GYM-PROG-PM1	Prise de masse – 1 mois	{"durée": "1 mois", "objectif": "Prise de masse"}	600000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
70	31	GYM-PROG-PM3	Prise de masse – 3 mois	{"durée": "3 mois", "objectif": "Prise de masse"}	1500000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
71	31	GYM-PROG-PP1	Perte de poids – 1 mois	{"durée": "1 mois", "objectif": "Perte de poids"}	600000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
72	31	GYM-PROG-PP3	Perte de poids – 3 mois	{"durée": "3 mois", "objectif": "Perte de poids"}	1500000.00	t	\N	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	0
73	32	GYM-COURS-1	Cours à l'unité	{"formule": "1 cours"}	65000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
74	32	GYM-COURS-10	10 cours	{"formule": "10 cours"}	550000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
75	32	GYM-COURS-M	Mensuel illimité	{"formule": "Mensuel illimité"}	480000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
76	33	SVC-MASS-R30	30 minutes	{"durée": "30 min"}	120000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
77	33	SVC-MASS-R60	60 minutes	{"durée": "60 min"}	210000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
78	33	SVC-MASS-R90	90 minutes	{"durée": "90 min"}	290000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
79	34	SVC-MASS-S45	45 minutes	{"durée": "45 min"}	180000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
80	34	SVC-MASS-S60	60 minutes	{"durée": "60 min"}	240000.00	t	\N	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	0
81	35	SVC-MASS-P60	60 minutes	{"durée": "60 min"}	280000.00	t	\N	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	0
82	35	SVC-MASS-P90	90 minutes	{"durée": "90 min"}	380000.00	t	\N	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	0
83	36	SVC-MASS-T60	60 minutes	{"durée": "60 min"}	250000.00	t	\N	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	0
84	36	SVC-MASS-T90	90 minutes	{"durée": "90 min"}	350000.00	t	\N	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	0
85	36	SVC-MASS-T120	120 minutes	{"durée": "120 min"}	440000.00	t	\N	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	0
\.


--
-- Data for Name: products; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.products (id, type, name, description, sku, price, is_published, is_active, credit_enabled, credit_duration_months, credit_installments_count, created_at, updated_at, slug, stock_quantity, low_stock_threshold, is_service, provider) FROM stdin;
1	simple	Laptop Pro 14	Ordinateur portable pour professionnels.	LAPTOP-PRO-14	12000000.00	t	t	t	6	6	2026-01-26 14:12:19	2026-01-26 14:12:19	\N	0	5	f	\N
3	variable	T-Shirt CAURISHOP	T-shirt avec variantes de taille et couleur.	\N	\N	t	t	t	3	3	2026-01-26 14:12:19	2026-01-26 14:12:19	\N	0	5	f	\N
4	variable	Iphone 18	Juste description	\N	\N	f	t	t	12	12	2026-03-29 15:50:47	2026-03-29 15:50:47	iphone-18	0	5	f	\N
30	variable	Abonnement salle de gym	Accès illimité à l'espace muscu, cardio, sauna et cours collectifs de FitZone Conakry. Équipements haut de gamme.	\N	\N	t	t	t	3	3	2026-04-12 16:23:51	2026-04-12 16:23:51	abonnement-salle-de-gym	0	0	t	FitZone Conakry
31	variable	Programme musculation personnalisé	Programme de musculation 100 % adapté à votre morphologie et vos objectifs. Suivi hebdomadaire inclus.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:51	2026-04-12 16:23:51	programme-musculation-personnalise	0	0	t	PowerGym Elite
32	variable	Cours collectifs fitness	Zumba, Step, Body Combat, Cycling ou HIIT. Cours en groupe animés par des coachs dynamiques et certifiés.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	cours-collectifs-fitness	0	0	t	FitZone Conakry
2	simple	Routeur 4G	Routeur 4G avec batterie.	ROUTER-4G	650000.00	t	t	f	\N	\N	2026-01-26 14:12:19	2026-04-12 15:48:58	\N	-4	5	f	\N
5	simple	Samsung Galaxy S25	Smartphone Samsung Galaxy S25 avec écran AMOLED 6,2 pouces, processeur Exynos 2500 et triple capteur photo 200 MP.	SAM-S25	7200000.00	t	t	t	6	6	2026-04-12 16:04:42	2026-04-12 16:04:42	samsung-galaxy-s25	25	5	f	\N
6	simple	AirPods Pro 2	Écouteurs sans fil Apple AirPods Pro 2e génération avec réduction de bruit active et son spatial personnalisé.	APP-PRO2	2100000.00	t	t	f	\N	\N	2026-04-12 16:04:42	2026-04-12 16:04:42	airpods-pro-2	40	5	f	\N
7	variable	iPad Pro 12.9"	Tablette Apple iPad Pro 12,9 pouces avec puce M4, écran Liquid Retina XDR et compatibilité Apple Pencil Pro.	\N	\N	t	t	t	12	12	2026-04-12 16:04:43	2026-04-12 16:04:43	ipad-pro-129	0	5	f	\N
8	variable	Clavier Mécanique RGB	Clavier mécanique gaming avec rétroéclairage RGB personnalisable, switches Cherry MX et repose-poignets magnétique.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:45	2026-04-12 16:04:45	clavier-mecanique-rgb	0	5	f	\N
9	variable	Disque SSD Externe	Disque SSD portable ultra-rapide, résistant aux chocs et à l'eau, avec interface USB-C 3.2 Gen 2 (1 000 Mo/s).	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:46	2026-04-12 16:04:46	disque-ssd-externe	0	5	f	\N
10	variable	Sneakers Nike Air Max	Chaussures de sport Nike Air Max avec amorti Air visible et semelle en caoutchouc durable. Confort au quotidien.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:47	2026-04-12 16:04:47	sneakers-nike-air-max	0	5	f	\N
11	simple	Montre Classique Or	Montre analogique élégante avec boîtier en acier inoxydable doré, bracelet en cuir véritable et verre saphir anti-rayures.	WATCH-GOLD	1750000.00	t	t	t	3	3	2026-04-12 16:04:47	2026-04-12 16:04:47	montre-classique-or	15	5	f	\N
12	variable	Sac à Main Cuir	Sac à main en cuir véritable de qualité supérieure, coutures renforcées, fermeture dorée et compartiments multiples.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	sac-a-main-cuir	0	5	f	\N
13	simple	Lunettes de Soleil Aviateur	Lunettes de soleil style aviateur avec monture en métal doré, verres polarisés UV400 et étui de protection inclus.	SUNGLASS-AVI	430000.00	t	t	f	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	lunettes-de-soleil-aviateur	35	5	f	\N
14	variable	Parfum Prestige	Eau de parfum de luxe aux notes de bois de santal, rose de Damas et musc blanc. Longue tenue 8 à 10 heures.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:48	2026-04-12 16:04:48	parfum-prestige	0	5	f	\N
15	variable	Café Arabica Premium	Café 100 % Arabica de haute altitude, torréfaction artisanale, notes de caramel et de fruits rouges. Origine Éthiopie.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:49	2026-04-12 16:04:49	cafe-arabica-premium	0	5	f	\N
16	simple	Huile d'Olive Extra Vierge	Huile d'olive extra vierge première pression à froid, acidité < 0,3 %, bouteille en verre foncé 750 ml. Origine Tunisie.	HUILE-OL-EV	215000.00	t	t	f	\N	\N	2026-04-12 16:04:50	2026-04-12 16:04:50	huile-dolive-extra-vierge	60	5	f	\N
17	variable	Miel Naturel Pur	Miel 100 % naturel non pasteurisé, récolté à la main par des apiculteurs locaux. Riche en antioxydants et enzymes.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:50	2026-04-12 16:04:50	miel-naturel-pur	0	5	f	\N
18	simple	Thé Vert Bio Sencha	Thé vert japonais Sencha biologique, riche en catéchines, goût végétal délicat. Boîte de 40 sachets.	THE-VERT-BIO	135000.00	t	t	f	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	the-vert-bio-sencha	80	5	f	\N
19	variable	Chocolat Artisanal	Tablettes de chocolat artisanal, préparées avec des fèves de cacao de première qualité. Sans huile de palme.	\N	\N	t	t	f	\N	\N	2026-04-12 16:04:51	2026-04-12 16:04:51	chocolat-artisanal	0	5	f	\N
20	simple	Soin du visage hydratant	Soin du visage en profondeur avec nettoyage, gommage, masque hydratant et sérum vitamine C. Durée : 60 min.	SOIN-DU-VISAGE-HYDRATANT	180000.00	t	t	f	\N	\N	2026-04-12 16:23:46	2026-04-12 16:23:46	soin-du-visage-hydratant	0	0	t	Beauty Palace
21	variable	Manucure & Pédicure	Soin complet des mains et pieds avec pose de vernis semi-permanent. Choix de la prestation selon vos besoins.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	manucure-pedicure	0	0	t	Beauty Palace
22	variable	Épilation corporelle	Épilation à la cire chaude ou froide, réalisée par des esthéticiennes professionnelles. Zones au choix.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	epilation-corporelle	0	0	t	Glam Studio
23	simple	Coiffure & Brushing	Coupe, soin kératine, brushing ou tressage afro réalisé par nos coiffeurs experts. Résultat naturel et longue durée.	COIFFURE-BRUSHING	120000.00	t	t	f	\N	\N	2026-04-12 16:23:47	2026-04-12 16:23:47	coiffure-brushing	0	0	t	Glam Studio
24	variable	Maquillage professionnel	Maquillage réalisé par une make-up artist certifiée. Produits premium longue tenue, idéal pour événements.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	maquillage-professionnel	0	0	t	Beauty Palace
25	variable	Coaching sportif personnel	Séances de coaching individuel avec un coach certifié. Programme sur-mesure : perte de poids, prise de masse, remise en forme.	\N	\N	t	t	t	3	3	2026-04-12 16:23:48	2026-04-12 16:23:48	coaching-sportif-personnel	0	0	t	Coach Pro Conakry
26	variable	Cours de natation	Cours de natation encadrés par des maîtres nageurs diplômés. Tous niveaux, de débutant à confirmé.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:48	2026-04-12 16:23:48	cours-de-natation	0	0	t	AquaFit Center
27	variable	Cours de yoga & méditation	Cours de yoga Hatha, Vinyasa ou méditation pleine conscience. Accessible à tous, tapis fourni.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	cours-de-yoga-meditation	0	0	t	Zen Yoga Studio
28	variable	Arts martiaux & self-défense	Cours de Taekwondo, Karaté ou Judo dispensés par des ceintures noires. Enfants et adultes bienvenus.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	arts-martiaux-self-defense	0	0	t	Combat Academy
29	variable	Cours de pilates	Pilates mat ou reformer pour renforcer la sangle abdominale, améliorer la posture et la souplesse. Petits groupes de 6 max.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:50	2026-04-12 16:23:50	cours-de-pilates	0	0	t	Coach Pro Conakry
33	variable	Massage relaxant	Massage suédois aux huiles essentielles pour relâcher les tensions musculaires et favoriser un profond état de détente.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	massage-relaxant	0	0	t	Studio Zen
34	variable	Massage sportif	Massage deep-tissue ciblant les groupes musculaires sollicités par le sport. Idéal avant ou après l'effort.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:52	2026-04-12 16:23:52	massage-sportif	0	0	t	Studio Zen
35	variable	Massage aux pierres chaudes	Massage thérapeutique utilisant des pierres volcaniques chauffées pour détendre les muscles en profondeur et rééquilibrer l'énergie.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	massage-aux-pierres-chaudes	0	0	t	Wellness Center
36	variable	Massage thaïlandais	Technique ancestrale alliant acupression et étirements passifs. Améliore la circulation, la souplesse et le bien-être global.	\N	\N	t	t	f	\N	\N	2026-04-12 16:23:53	2026-04-12 16:23:53	massage-thailandais	0	0	t	Wellness Center
37	simple	Forfait Spa journée complète	Journée bien-être incluant : accueil tisane, bain à remous, hammam, massage 60 min et soin du visage. Pour 1 personne.	FORFAIT-SPA-JOURNEE-COMPLETE	850000.00	t	t	t	2	2	2026-04-12 16:23:53	2026-04-12 16:23:53	forfait-spa-journee-complete	0	0	t	Studio Zen
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
1	1
2	1
3	1
4	1
5	1
6	1
7	1
8	1
9	1
10	1
11	1
12	1
13	1
14	1
15	1
16	1
17	1
18	1
19	1
20	1
21	1
22	1
23	1
24	1
25	1
26	1
27	1
28	1
29	1
30	1
31	1
32	1
33	1
21	2
22	2
23	2
24	2
25	2
27	2
28	2
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
1	admin	web	2026-01-26 14:12:18	2026-01-26 14:12:18
2	employee	web	2026-01-26 14:12:18	2026-01-26 14:12:18
3	company_admin	web	2026-04-12 13:55:29	2026-04-12 13:55:29
4	company_employee	web	2026-04-12 13:55:29	2026-04-12 13:55:29
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
5oFk6byIAXbGDBKP3yJaUygvtjp46pHRhPWzIbVE	1	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiZWFVMW9ZNEZ4SUwxNkJMcVVNZXM0MkVmeVF6N05KWXhIdkx5ajI2UCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9jb21wYW5pZXMiO3M6NToicm91dGUiO3M6MjE6ImFkbWluLmNvbXBhbmllcy5pbmRleCI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==	1777120475
BJnRl3rbSK11P4tbi6FXPrV7uNWpD1viHOPRa6Bh	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoibk9nbzVPelVRQmVuWmQ3Mk9PNExpcFRuUk1iTXFDNWpqWE1pWWROOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1777120541
7hFWGA9DORCAQsD545tIZuOU7YSBX6b8s4J6vVHq	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiMVJRU2hEejAwaW43VGFIaFZsRWxkRDY0d0VKcWdHQmdYWmlXTWEyTSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9kZW1hcnJlciI7czo1OiJyb3V0ZSI7czoxMToiZ2V0LXN0YXJ0ZWQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1777143714
9WuSObaQHfW69ZqDJwdfvJZ7FR4GsldsrGkGl9Nb	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiUXU4UHVUT0tpcXY5UVY2VzR2Z2FNS1dmTzhxSWI0TFpjNHk1QUIwWSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo0MToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2FkbWluL25vdGlmaWNhdGlvbnMiO31zOjk6Il9wcmV2aW91cyI7YToyOntzOjM6InVybCI7czoyNzoiaHR0cDovLzEyNy4wLjAuMTo4MDAwL2xvZ2luIjtzOjU6InJvdXRlIjtzOjU6ImxvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1777186031
m5eqjA9kFtyVlUU7ewMZLysfrc16XOVGMM8Gosbb	1	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	YTo0OntzOjY6Il90b2tlbiI7czo0MDoiU244b0pWd2haZDFFS2MyOGNvcGl3YUZpUWFJQ1B4eG84MnBKc2NWVSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJuZXciO2E6MDp7fXM6Mzoib2xkIjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbiI7czo1OiJyb3V0ZSI7czoxNToiYWRtaW4uZGFzaGJvYXJkIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9	1777667289
q59QF5OT54OhWAp3veeMxnW6rNlz9Gq5qFovTxix	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWldCbnhES3MwbTF1TlowNkRpRlhNTjR6ZFgwU2QzMThWZXRybDdJYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7czo0OiJob21lIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1777666180
\.


--
-- Data for Name: settings; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.settings (id, key, value, "group", type, label, description, created_at, updated_at) FROM stdin;
1	app_name	CauriShop	general	text	Nom de l'application	Affiché dans les emails et l'interface.	2026-04-12 13:37:47	2026-04-12 13:37:47
2	app_currency	GNF	general	text	Devise	Devise utilisée pour les montants.	2026-04-12 13:37:47	2026-04-12 13:37:47
3	app_timezone	Africa/Conakry	general	text	Fuseau horaire	\N	2026-04-12 13:37:47	2026-04-12 13:37:47
4	app_contact_email	contact@caurishop.gn	general	text	Email de contact	Adresse email affichée aux clients.	2026-04-12 13:37:47	2026-04-12 13:37:47
5	app_contact_phone	+224 000 000 000	general	text	Téléphone de contact	\N	2026-04-12 13:37:47	2026-04-12 13:37:47
6	app_address	Conakry, Guinée	general	textarea	Adresse	\N	2026-04-12 13:37:47	2026-04-12 13:37:47
7	credit_default_interest_rate	0	credit	number	Taux d'intérêt par défaut (%)	Taux appliqué si la company n'a pas de taux propre.	2026-04-12 13:37:47	2026-04-12 13:37:47
8	credit_max_months	24	credit	number	Durée maximale du crédit (mois)	\N	2026-04-12 13:37:47	2026-04-12 13:37:47
9	credit_min_down_payment_percent	10	credit	number	Acompte minimum (%)	Pourcentage minimum de l'acompte sur le total.	2026-04-12 13:37:47	2026-04-12 13:37:47
10	credit_global_limit	50000000	credit	number	Plafond de crédit global (GNF)	Montant maximal total en crédit en cours.	2026-04-12 13:37:47	2026-04-12 13:37:47
11	credit_late_penalty_rate	2	credit	number	Pénalité de retard (%)	Pourcentage appliqué sur l'échéance en retard.	2026-04-12 13:37:47	2026-04-12 13:37:47
12	stock_low_threshold	5	stock	number	Seuil de stock bas (unités)	Une alerte est émise quand le stock passe sous ce seuil.	2026-04-12 13:37:47	2026-04-12 13:37:47
13	stock_alert_enabled	1	stock	boolean	Activer les alertes de stock bas	\N	2026-04-12 13:37:47	2026-04-12 13:37:47
14	notif_new_order	1	notifications	boolean	Nouvelle commande	Notifier quand une nouvelle commande est créée.	2026-04-12 13:37:47	2026-04-12 13:37:47
16	notif_installment_late	1	notifications	boolean	Échéance en retard	Notifier quand une échéance passe en statut "late".	2026-04-12 13:37:47	2026-04-12 13:37:47
17	notif_days_before_due	3	notifications	number	Jours avant échéance pour rappel	Envoyer un rappel X jours avant la date d'échéance.	2026-04-12 13:37:47	2026-04-12 13:37:47
15	notif_payment_received	1	notifications	boolean	Paiement reçu	Notifier quand un paiement est enregistré.	2026-04-12 13:37:47	2026-04-12 13:42:38
\.


--
-- Data for Name: transactions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.transactions (id, type, amount, order_id, payment_id, metadata, created_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, is_active, company_id) FROM stdin;
1	CAURISHOP Admin	admin@caurishop.test	\N	$2y$12$.k7shCERUcvZQa12GpO.CuIJV76aT6biyFNOmTYX9NGplaOJQ8lSO	\N	2026-01-26 14:12:19	2026-01-26 14:12:19	t	\N
2	CAURISHOP Employee	employee@caurishop.test	\N	$2y$12$UpaHmxp73qlNNdPzMqXFkukhuJGkv46wJQ28IuGSe8ejOjyWdydE2	\N	2026-01-26 14:12:19	2026-04-12 13:15:16	t	\N
5	Fatoumata Camara	admin@entreprise-a.test	\N	$2y$12$tN62WVQz3w34ZfZrmnTzLO.2wPKH11.PIW9QC5W2zVhYiJxXKhdJa	\N	2026-04-12 14:10:20	2026-04-12 14:10:20	t	1
6	Ibrahima Bah	ibrahima@entreprise-a.test	\N	$2y$12$dORewxdnrhtWf5RYPGAnseIFTXUYnsPyHuY/EzFWLNJ2vmDypfsCW	\N	2026-04-12 14:10:20	2026-04-12 14:10:20	t	1
7	Mariama Sow	mariama@entreprise-a.test	\N	$2y$12$gbVOjXM/mNHsnDDkFBrC6OPoZYCCjqnaWtGG7h9yftVEEzSSqWxIW	\N	2026-04-12 14:10:20	2026-04-12 14:10:20	t	1
8	Oumar Diallo	admin@entreprise-b.test	\N	$2y$12$wPm3zlmqHWFXcWU1auAKNuzVq8IDV2lfhVdSDAycnnTYKJgi26mFi	\N	2026-04-12 14:10:20	2026-04-12 14:10:20	t	2
9	Aissatou Barry	aissatou@entreprise-b.test	\N	$2y$12$MRXNSmMn7Woem2bXPEs3ZeCrTTf6UWs3dARBIJHNmM/NQp6CXno7e	\N	2026-04-12 14:10:20	2026-04-12 14:10:20	t	2
\.


--
-- Name: activity_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activity_logs_id_seq', 38, true);


--
-- Name: app_notifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.app_notifications_id_seq', 5, true);


--
-- Name: companies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.companies_id_seq', 7, true);


--
-- Name: credit_plans_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.credit_plans_id_seq', 4, true);


--
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.customers_id_seq', 3, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: installments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.installments_id_seq', 34, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 32, true);


--
-- Name: order_items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.order_items_id_seq', 7, true);


--
-- Name: payment_allocations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payment_allocations_id_seq', 3, true);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 33, true);


--
-- Name: product_images_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.product_images_id_seq', 92, true);


--
-- Name: product_variants_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.product_variants_id_seq', 85, true);


--
-- Name: products_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.products_id_seq', 37, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 4, true);


--
-- Name: settings_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.settings_id_seq', 17, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 9, true);


--
-- Name: activity_logs activity_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_pkey PRIMARY KEY (id);


--
-- Name: app_notifications app_notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_notifications
    ADD CONSTRAINT app_notifications_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: companies companies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.companies
    ADD CONSTRAINT companies_pkey PRIMARY KEY (id);


--
-- Name: credit_plans credit_plans_order_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.credit_plans
    ADD CONSTRAINT credit_plans_order_id_unique UNIQUE (order_id);


--
-- Name: credit_plans credit_plans_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.credit_plans
    ADD CONSTRAINT credit_plans_pkey PRIMARY KEY (id);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: installments installments_credit_plan_id_installment_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.installments
    ADD CONSTRAINT installments_credit_plan_id_installment_number_unique UNIQUE (credit_plan_id, installment_number);


--
-- Name: installments installments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.installments
    ADD CONSTRAINT installments_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: order_items order_items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_pkey PRIMARY KEY (id);


--
-- Name: orders orders_order_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_order_number_unique UNIQUE (order_number);


--
-- Name: orders orders_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payment_allocations payment_allocations_payment_id_installment_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_allocations
    ADD CONSTRAINT payment_allocations_payment_id_installment_id_unique UNIQUE (payment_id, installment_id);


--
-- Name: payment_allocations payment_allocations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_allocations
    ADD CONSTRAINT payment_allocations_pkey PRIMARY KEY (id);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: product_images product_images_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_images
    ADD CONSTRAINT product_images_pkey PRIMARY KEY (id);


--
-- Name: product_variants product_variants_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_pkey PRIMARY KEY (id);


--
-- Name: product_variants product_variants_sku_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_sku_unique UNIQUE (sku);


--
-- Name: products products_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_pkey PRIMARY KEY (id);


--
-- Name: products products_sku_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_sku_unique UNIQUE (sku);


--
-- Name: products products_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.products
    ADD CONSTRAINT products_slug_unique UNIQUE (slug);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: settings settings_key_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_key_unique UNIQUE (key);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: transactions transactions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: customers_type_company_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX customers_type_company_id_index ON public.customers USING btree (type, company_id);


--
-- Name: installments_due_date_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX installments_due_date_status_index ON public.installments USING btree (due_date, status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: order_items_order_id_product_id_variant_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX order_items_order_id_product_id_variant_id_index ON public.order_items USING btree (order_id, product_id, variant_id);


--
-- Name: orders_customer_id_order_type_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX orders_customer_id_order_type_status_index ON public.orders USING btree (customer_id, order_type, status);


--
-- Name: payments_customer_id_payment_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX payments_customer_id_payment_date_index ON public.payments USING btree (customer_id, payment_date);


--
-- Name: product_images_product_id_sort_order_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX product_images_product_id_sort_order_index ON public.product_images USING btree (product_id, sort_order);


--
-- Name: product_variants_product_id_is_active_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX product_variants_product_id_is_active_index ON public.product_variants USING btree (product_id, is_active);


--
-- Name: products_type_is_published_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX products_type_is_published_index ON public.products USING btree (type, is_published);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: transactions_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX transactions_type_index ON public.transactions USING btree (type);


--
-- Name: activity_logs activity_logs_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_logs
    ADD CONSTRAINT activity_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: app_notifications app_notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.app_notifications
    ADD CONSTRAINT app_notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: credit_plans credit_plans_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.credit_plans
    ADD CONSTRAINT credit_plans_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: customers customers_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- Name: customers customers_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: installments installments_credit_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.installments
    ADD CONSTRAINT installments_credit_plan_id_foreign FOREIGN KEY (credit_plan_id) REFERENCES public.credit_plans(id) ON DELETE CASCADE;


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE CASCADE;


--
-- Name: order_items order_items_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE RESTRICT;


--
-- Name: order_items order_items_variant_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.order_items
    ADD CONSTRAINT order_items_variant_id_foreign FOREIGN KEY (variant_id) REFERENCES public.product_variants(id) ON DELETE SET NULL;


--
-- Name: orders orders_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: orders orders_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.orders
    ADD CONSTRAINT orders_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE RESTRICT;


--
-- Name: payment_allocations payment_allocations_installment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_allocations
    ADD CONSTRAINT payment_allocations_installment_id_foreign FOREIGN KEY (installment_id) REFERENCES public.installments(id) ON DELETE CASCADE;


--
-- Name: payment_allocations payment_allocations_payment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payment_allocations
    ADD CONSTRAINT payment_allocations_payment_id_foreign FOREIGN KEY (payment_id) REFERENCES public.payments(id) ON DELETE CASCADE;


--
-- Name: payments payments_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: payments payments_credit_plan_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_credit_plan_id_foreign FOREIGN KEY (credit_plan_id) REFERENCES public.credit_plans(id) ON DELETE SET NULL;


--
-- Name: payments payments_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE RESTRICT;


--
-- Name: payments payments_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE SET NULL;


--
-- Name: product_images product_images_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_images
    ADD CONSTRAINT product_images_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: product_variants product_variants_product_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.product_variants
    ADD CONSTRAINT product_variants_product_id_foreign FOREIGN KEY (product_id) REFERENCES public.products(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: transactions transactions_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: transactions transactions_order_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_order_id_foreign FOREIGN KEY (order_id) REFERENCES public.orders(id) ON DELETE SET NULL;


--
-- Name: transactions transactions_payment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.transactions
    ADD CONSTRAINT transactions_payment_id_foreign FOREIGN KEY (payment_id) REFERENCES public.payments(id) ON DELETE SET NULL;


--
-- Name: users users_company_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_company_id_foreign FOREIGN KEY (company_id) REFERENCES public.companies(id) ON DELETE SET NULL;


--
-- PostgreSQL database dump complete
--

\unrestrict kEpPfsTGyIJMEQPnoGgQ72gX2sEH83Y9quyCcDpBOd4a6CPXpRBPfu8cgGEoqfU

