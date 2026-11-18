import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { useCreateClientMutation, useUpdateClientMutation } from '../../client/clientApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import type { Client } from '../../types/client';
import { validateClient } from '../../validators/clientValidator';
import type { ClientFormValues } from '../../validators/clientValidator';
import FormField from '../SharedComponents/FormField';

interface ClientFormProps {
  /** Given = edit this client; left out = create a new one. */
  client?: Client;
  onSaved: () => void;
}

export default function ClientForm({ client, onSaved }: ClientFormProps) {
  const [values, setValues] = useState<ClientFormValues>({
    name: client?.name ?? '',
    phone_number: client?.phone_number ?? '',
    email: client?.email ?? '',
    last_follow_up_date: client?.last_follow_up_date ?? '',
  });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  const [createClient, createState] = useCreateClientMutation();
  const [updateClient, updateState] = useUpdateClientMutation();
  const { isLoading, error } = client ? updateState : createState;

  const errors = { ...getFieldErrors(error), ...validationErrors };
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleChange(event: ChangeEvent<HTMLInputElement>) {
    setValues({ ...values, [event.target.name]: event.target.value });
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const newErrors = validateClient(values);
    setValidationErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      return;
    }

    const payload = {
      name: values.name.trim(),
      phone_number: values.phone_number.trim(),
      email: values.email.trim() || null,
      last_follow_up_date: values.last_follow_up_date || null,
    };

    const result = client
      ? await updateClient({ id: client.id, changes: payload })
      : await createClient(payload);

    if (!result.error) {
      onSaved();
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      <FormField label="Name" htmlFor="name" error={errors.name}>
        <input id="name" name="name" value={values.name} onChange={handleChange} className="form-input" />
      </FormField>

      <FormField label="Phone number" htmlFor="phone_number" error={errors.phone_number}>
        <input
          id="phone_number"
          name="phone_number"
          type="tel"
          value={values.phone_number}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      <FormField label="Email (optional)" htmlFor="email" error={errors.email}>
        <input id="email" name="email" type="email" value={values.email} onChange={handleChange} className="form-input" />
      </FormField>

      <FormField label="Last follow-up date (optional)" htmlFor="last_follow_up_date" error={errors.last_follow_up_date}>
        <input
          id="last_follow_up_date"
          name="last_follow_up_date"
          type="date"
          value={values.last_follow_up_date}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <div className="flex justify-end">
        <button type="submit" disabled={isLoading} className="btn-primary">
          {client ? 'Save changes' : 'Create client'}
        </button>
      </div>
    </form>
  );
}
