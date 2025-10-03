<?php declare(strict_types=1);

namespace App\Modules\Customer;

use Error;
use Exception;
use App\Modules\Customer\Dto\CreateCustomerRequestDto;
use App\Lib\Success;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

final class Service
{
    public function __construct(
        private Repository $repo,
        private Factory $factory
    ) {}

    public function save(CreateCustomerRequestDto $data): Error|Success
    {
        $customer = $this->factory->create(
            $data->firstName,
            $data->surName
        );

        try {
            $this->repo->save($customer, true);
        } catch (UniqueConstraintViolationException) {
            return new Error("UniqueConstraintViolation", 400);
        } catch (Exception $e) {
            return new Error($e->getMessage(), 500);
        }

        return new Success("CustomerCreated");
    }
}
