<?php

namespace App\Services\Admin;

use App\Repositories\Admin\CustomerRepository;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerService
{
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {}

    public function getPaginatedCustomers(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->customerRepository->paginate($perPage, $filters);
    }

    public function getAllCustomers(): Collection
    {
        return $this->customerRepository->all();
    }

    public function getCustomerById(int $id): ?Customer
    {
        return $this->customerRepository->findById($id);
    }

    public function createCustomer(array $data): Customer
    {
        DB::beginTransaction();

        try {
            $data['is_active'] = $data['is_active'] ?? true;

            if ($data['type'] === Customer::TYPE_INDIVIDUAL) {
                $data['company_id'] = null;
                $data['company_contact_name'] = null;
            } else {
                $data['first_name'] = null;
                $data['last_name'] = null;
            }

            $customer = $this->customerRepository->create($data);

            DB::commit();

            return $customer;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateCustomer(Customer $customer, array $data): Customer
    {
        DB::beginTransaction();

        try {
            if ($data['type'] === Customer::TYPE_INDIVIDUAL) {
                $data['company_id'] = null;
                $data['company_contact_name'] = null;
            } else {
                $data['first_name'] = null;
                $data['last_name'] = null;
            }

            $this->customerRepository->update($customer, $data);

            DB::commit();

            return $customer->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteCustomer(Customer $customer): bool
    {
        if ($customer->orders()->count() > 0) {
            throw new \Exception('Impossible de supprimer un client avec des commandes associées.');
        }

        return $this->customerRepository->delete($customer);
    }

    public function activateCustomer(Customer $customer): bool
    {
        if ($customer->is_active) {
            throw new \Exception('Le client est déjà actif.');
        }

        return $this->customerRepository->activate($customer);
    }

    public function deactivateCustomer(Customer $customer): bool
    {
        if (!$customer->is_active) {
            throw new \Exception('Le client est déjà désactivé.');
        }

        return $this->customerRepository->deactivate($customer);
    }

    public function toggleCustomerStatus(Customer $customer): bool
    {
        return $customer->is_active
            ? $this->deactivateCustomer($customer)
            : $this->activateCustomer($customer);
    }

    public function emailExists(string $email, ?int $excludeCustomerId = null): bool
    {
        $customer = $this->customerRepository->findByEmail($email);

        if (!$customer) {
            return false;
        }

        return $excludeCustomerId ? $customer->id !== $excludeCustomerId : true;
    }
}
