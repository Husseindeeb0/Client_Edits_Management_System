import { useState } from 'react';
import { useGetClientDetailsQuery, useGetPaginatedClientsQuery } from '../../client/clientApi';
import SearchSelect from '../SharedComponents/SearchSelect';

interface ClientSelectProps {
  id: string;
  value: number | null;
  onChange: (clientId: number) => void;
}

/** Choose a client: searchable, one page of clients at a time. */
export default function ClientSelect({ id, value, onChange }: ClientSelectProps) {
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [pickedLabel, setPickedLabel] = useState<string>();

  const { data, isFetching } = useGetPaginatedClientsQuery({ search, page });
  // The current client may not be on this page, so get its name on its own (when editing)
  const { data: current } = useGetClientDetailsQuery(value ?? 0, { skip: value === null || pickedLabel !== undefined });

  return (
    <SearchSelect
      id={id}
      placeholder="Choose a client"
      selectedId={value}
      selectedLabel={pickedLabel ?? current?.name}
      options={(data?.data ?? []).map((client) => ({ id: client.id, label: client.name, hint: client.phone_number }))}
      isLoading={isFetching}
      page={data?.meta.current_page ?? 1}
      lastPage={data?.meta.last_page ?? 1}
      onPageChange={setPage}
      onSearch={(term) => {
        setSearch(term);
        setPage(1);
      }}
      onSelect={(option) => {
        setPickedLabel(option.label);
        onChange(option.id);
      }}
    />
  );
}
