import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { useChangePasswordMutation } from '../../client/authApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import { validatePassword } from '../../validators/profileValidator';
import type { PasswordFormValues } from '../../validators/profileValidator';
import FormField from '../SharedComponents/FormField';

const EMPTY_VALUES: PasswordFormValues = { current_password: '', new_password: '', new_password_confirmation: '' };

export default function ChangePasswordForm() {
  const [values, setValues] = useState<PasswordFormValues>(EMPTY_VALUES);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  const [isSaved, setIsSaved] = useState(false);

  const [changePassword, { isLoading, error }] = useChangePasswordMutation();

  const errors = { ...getFieldErrors(error), ...validationErrors };
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleChange(event: ChangeEvent<HTMLInputElement>) {
    setValues({ ...values, [event.target.name]: event.target.value });
    setIsSaved(false);
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const newErrors = validatePassword(values);
    setValidationErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      return;
    }

    // The confirmation is only checked above, it isn't sent
    const result = await changePassword({
      current_password: values.current_password,
      new_password: values.new_password,
    });

    if (!result.error) {
      setValues(EMPTY_VALUES);
      setIsSaved(true);
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      <FormField label="Current password" htmlFor="current_password" error={errors.current_password}>
        <input
          id="current_password"
          name="current_password"
          type="password"
          autoComplete="current-password"
          value={values.current_password}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      <FormField label="New password" htmlFor="new_password" error={errors.new_password}>
        <input
          id="new_password"
          name="new_password"
          type="password"
          autoComplete="new-password"
          value={values.new_password}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      <FormField label="Confirm new password" htmlFor="new_password_confirmation" error={errors.new_password_confirmation}>
        <input
          id="new_password_confirmation"
          name="new_password_confirmation"
          type="password"
          autoComplete="new-password"
          value={values.new_password_confirmation}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <div className="flex items-center justify-end gap-3">
        {isSaved && <p className="text-sm text-teal-400">Password changed.</p>}
        <button type="submit" disabled={isLoading} className="btn-primary">
          Change password
        </button>
      </div>
    </form>
  );
}