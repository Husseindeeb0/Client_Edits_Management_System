import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { useGetPaginatedClientsQuery } from '../../client/clientApi';
import { useCreateCompanyMutation, useUpdateCompanyMutation } from '../../client/companyApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import type { Company } from '../../types/company';
import { validateCompany } from '../../validators/companyValidator';
import type { CompanyFormValues } from '../../validators/companyValidator';
import FormField from '../SharedComponents/FormField';

interface CompanyFormProps {
  /** Given = edit this company; left out = create a new one. */
  company?: Company;
  onSaved: () => void;
}

export default function CompanyForm({ company, onSaved }: CompanyFormProps) {
  const [values, setValues] = useState<CompanyFormValues>({
    name: company?.name ?? '',
    client_id: company ? String(company.client_id) : '',
  });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  // For the dropdown; 100 is the most the API returns per page
  const { data: clients } = useGetPaginatedClientsQuery({ per_page: 100 });

  const [createCompany, createState] = useCreateCompanyMutation();
  const [updateCompany, updateState] = useUpdateCompanyMutation();
  const { isLoading, error } = company ? updateState : createState;

  const errors = { ...getFieldErrors(error), ...validationErrors };
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleChange(event: ChangeEvent<HTMLInputElement | HTMLSelectElement>) {
    setValues({ ...values, [event.target.name]: event.target.value });
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const newErrors = validateCompany(values);
    setValidationErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      return;
    }

    const payload = { name: values.name.trim(), client_id: Number(values.client_id) };

    const result = company
      ? await updateCompany({ id: company.id, changes: payload })
      : await createCompany(payload);

    if (!result.error) {
      onSaved();
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      <FormField label="Name" htmlFor="name" error={errors.name}>
        <input id="name" name="name" value={values.name} onChange={handleChange} className="form-input" />
      </FormField>

      <FormField label="Client" htmlFor="client_id" error={errors.client_id}>
        <select id="client_id" name="client_id" value={values.client_id} onChange={handleChange} className="form-input">
          <option value="">{clients ? 'Choose a client' : 'Loading clients…'}</option>
          {clients?.data.map((client) => (
            <option key={client.id} value={client.id}>
              {client.name}
            </option>
          ))}
        </select>
      </FormField>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <div className="flex justify-end">
        <button type="submit" disabled={isLoading} className="btn-primary">
          {company ? 'Save changes' : 'Create company'}
        </button>
      </div>
    </form>
  );
}
