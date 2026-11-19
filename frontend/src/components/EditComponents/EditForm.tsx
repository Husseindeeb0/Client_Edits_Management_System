import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { useCreateEditMutation, useUpdateEditMutation } from '../../client/editApi';
import { getErrorMessage, getFieldErrors, isApiError } from '../../errors/apiError';
import { EDIT_PRIORITIES, EDIT_STATUSES } from '../../types/edit';
import type { Edit } from '../../types/edit';
import { validateEdit } from '../../validators/editValidator';
import type { EditFormValues } from '../../validators/editValidator';
import ProjectSelect from '../ProjectsComponents/ProjectSelect';
import FormField from '../SharedComponents/FormField';

interface EditFormProps {
  /** Given = edit this edit; left out = create a new one. */
  edit?: Edit;
  /** Pre-selects the project when creating from a project's page. */
  defaultProjectId?: number;
  onSaved: () => void;
}

export default function EditForm({ edit, defaultProjectId, onSaved }: EditFormProps) {
  const [values, setValues] = useState<EditFormValues>({
    project_id: String(edit?.project_id ?? defaultProjectId ?? ''),
    title: edit?.title ?? '',
    description: edit?.description ?? '',
    priority: edit?.priority ?? 'Medium',
    status: edit?.status ?? 'Pending',
    price: edit?.price != null ? String(edit.price) : '',
    is_paid: edit?.is_paid ?? false,
    due_date: edit?.due_date ?? '',
  });
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});

  const [createEdit, createState] = useCreateEditMutation();
  const [updateEdit, updateState] = useUpdateEditMutation();
  const { isLoading, error } = edit ? updateState : createState;

  const errors = { ...getFieldErrors(error), ...validationErrors };
  const formError = error && !(isApiError(error) && error.status === 422) ? getErrorMessage(error) : null;

  function handleChange(event: ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>) {
    setValues({ ...values, [event.target.name]: event.target.value });
  }

  async function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    const newErrors = validateEdit(values);
    setValidationErrors(newErrors);
    if (Object.keys(newErrors).length > 0) {
      return;
    }

    const payload = {
      project_id: Number(values.project_id),
      title: values.title.trim(),
      description: values.description.trim() || null,
      priority: values.priority,
      status: values.status,
      price: values.price === '' ? null : Number(values.price),
      is_paid: values.is_paid,
      due_date: values.due_date || null,
    };

    const result = edit ? await updateEdit({ id: edit.id, changes: payload }) : await createEdit(payload);

    if (!result.error) {
      onSaved();
    }
  }

  return (
    <form onSubmit={handleSubmit} noValidate className="space-y-4">
      <FormField label="Project" htmlFor="project_id" error={errors.project_id}>
        <ProjectSelect
          id="project_id"
          value={values.project_id ? Number(values.project_id) : null}
          onChange={(projectId) => setValues({ ...values, project_id: String(projectId) })}
        />
      </FormField>

      <FormField label="Title" htmlFor="title" error={errors.title}>
        <input id="title" name="title" value={values.title} onChange={handleChange} className="form-input" />
      </FormField>

      <FormField label="Description (optional)" htmlFor="description" error={errors.description}>
        <textarea
          id="description"
          name="description"
          rows={3}
          value={values.description}
          onChange={handleChange}
          className="form-input"
        />
      </FormField>

      <div className="grid gap-4 sm:grid-cols-2">
        <FormField label="Status" htmlFor="status" error={errors.status}>
          <select id="status" name="status" value={values.status} onChange={handleChange} className="form-input">
            {EDIT_STATUSES.map((status) => (
              <option key={status} value={status}>
                {status}
              </option>
            ))}
          </select>
        </FormField>

        <FormField label="Priority" htmlFor="priority" error={errors.priority}>
          <select id="priority" name="priority" value={values.priority} onChange={handleChange} className="form-input">
            {EDIT_PRIORITIES.map((priority) => (
              <option key={priority} value={priority}>
                {priority}
              </option>
            ))}
          </select>
        </FormField>

        <FormField label="Price (optional)" htmlFor="price" error={errors.price}>
          <input
            id="price"
            name="price"
            type="number"
            min="0"
            step="0.01"
            value={values.price}
            onChange={handleChange}
            className="form-input"
          />
        </FormField>

        <FormField label="Due date (optional)" htmlFor="due_date" error={errors.due_date}>
          <input id="due_date" name="due_date" type="date" value={values.due_date} onChange={handleChange} className="form-input" />
        </FormField>
      </div>

      <label className="flex items-center gap-2 text-sm text-slate-300">
        <input
          type="checkbox"
          checked={values.is_paid}
          onChange={(event) => setValues({ ...values, is_paid: event.target.checked })}
        />
        Paid
      </label>

      {formError && (
        <p className="rounded-md bg-red-500/10 px-3 py-2 text-sm text-red-300" role="alert">
          {formError}
        </p>
      )}

      <div className="flex justify-end">
        <button type="submit" disabled={isLoading} className="btn-primary">
          {edit ? 'Save changes' : 'Create edit'}
        </button>
      </div>
    </form>
  );
}
