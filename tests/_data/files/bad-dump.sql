-- The following statement inserts a new row into the employees table.
INSERT INTO employees
   (employee_id, last_name, email, hire_date, job_id)
   VALUES
   (employees_seq.NEXTVAL, 'Doe', 'JDOE', SYSDATE, 'IT_PROG');
