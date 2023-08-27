-------------------------------- MODULE ork_loop --------------------------------
EXTENDS TLC, Integers, FiniteSets, Sequences
CONSTANTS Loop, Workers, Resources, Parallelism, FastFailure, NULL

Min(set) == CHOOSE x \in set: \A y \in set \ {x}: y >= x

(*--algorithm ork_loop
variables
    \* Global vars.
    workers_count = Cardinality(Workers),
    emitted_output = 0,
    exit_status = [x \in Workers |-> NULL],
    signal = [x \in Workers |-> NULL],
    worker_resources \in [Workers -> SUBSET Resources],
    \* Loop vars.
    bootstrap_cycles = 0,
    bootstrap_max_cycles = Min({Parallelism, workers_count}),
    started = {},
    exited = {},
    run_times = [x \in Workers |-> 0],
    collected_output = 0;

define
    RunningWorkers == {x \in started: x \notin exited}
    BusyResources(I) == UNION {worker_resources[x]: x \in RunningWorkers \ I}
    RunnableWorkers(I) == {w \in Workers: 
        /\ w \notin started 
        /\ w \notin exited 
        /\ w \notin I
        /\ Cardinality(worker_resources[w] \intersect BusyResources(I)) = 0
    }
end define;

macro start_worker(I) begin
    if Cardinality(RunnableWorkers(I))  > 0 then
        with
            worker = CHOOSE x \in RunnableWorkers(I): TRUE
        do
            started := started \cup {worker};
            signal[worker] := "RUN";
        end with;
    end if;
end macro;

fair process loop = Loop begin
    Bootstrap:
        while bootstrap_cycles < bootstrap_max_cycles do
            bootstrap_cycles := bootstrap_cycles + 1;
            start_worker({});
        end while;
    Cycle:
        while Cardinality(exited) < workers_count do
            with 
                w \in RunningWorkers,
                status = exit_status[w],
                over_time = run_times[w] > 2,
                fail = FastFailure /\ (over_time \/ status = 1)
            do
                \* Empty the workers output pipe.
                collected_output := emitted_output;
                emitted_output := 0;
                \* Increase the process recorded run time.
                run_times[w] := run_times[w] + 1;

                if fail then
                    \* Kill all running workers.
                    exit_status := [w |-> status]
                        @@ [x \in RunningWorkers |-> 9]
                        @@ exit_status;
                    exited := exited \cup RunningWorkers;
                    goto Complete;
                else
                    if(status \in {0, 1}) then
                        exited := exited \cup {w};
                        start_worker({w});
                    else
                        if over_time then
                            \* Kill the worker.
                            exit_status[w] := 9;
                            exited := exited \cup {w};
                            start_worker({w});
                        end if;
                    end if;
                end if;
            end with;
        end while;
    Complete:
        assert(Cardinality(started) >= 1);
        assert(Cardinality(started) = Cardinality(exited));
        assert(Cardinality(RunningWorkers) = 0);
        assert(emitted_output = 0);
        if FastFailure = FALSE then
            assert(Cardinality(started) = workers_count);
        end if;
end process;

process worker \in Workers begin
    Work:
        await signal[self] = "RUN";

        \* Handle KILL signal.
        if exit_status[self] /= NULL then
            goto Done;
        else 
            emitted_output := emitted_output + 1;
        end if;
    Exit:
        \* Handle KILL signal.
        if exit_status[self] /= NULL then
            goto Done;
        else
            emitted_output := emitted_output + 1;
            either
                exit_status[self] := 0;
            or
                exit_status[self] := 1;
            end either;
        end if;
end process;

end algorithm;*)
\* BEGIN TRANSLATION (chksum(pcal) = "2cda2f08" /\ chksum(tla) = "e79bd59")
VARIABLES workers_count, emitted_output, exit_status, signal, 
          worker_resources, bootstrap_cycles, bootstrap_max_cycles, started, 
          exited, run_times, collected_output, pc

(* define statement *)
RunningWorkers == {x \in started: x \notin exited}
BusyResources(I) == UNION {worker_resources[x]: x \in RunningWorkers \ I}
RunnableWorkers(I) == {w \in Workers:
    /\ w \notin started
    /\ w \notin exited
    /\ w \notin I
    /\ Cardinality(worker_resources[w] \intersect BusyResources(I)) = 0
}


vars == << workers_count, emitted_output, exit_status, signal, 
           worker_resources, bootstrap_cycles, bootstrap_max_cycles, started, 
           exited, run_times, collected_output, pc >>

ProcSet == {Loop} \cup (Workers)

Init == (* Global variables *)
        /\ workers_count = Cardinality(Workers)
        /\ emitted_output = 0
        /\ exit_status = [x \in Workers |-> NULL]
        /\ signal = [x \in Workers |-> NULL]
        /\ worker_resources \in [Workers -> SUBSET Resources]
        /\ bootstrap_cycles = 0
        /\ bootstrap_max_cycles = Min({Parallelism, workers_count})
        /\ started = {}
        /\ exited = {}
        /\ run_times = [x \in Workers |-> 0]
        /\ collected_output = 0
        /\ pc = [self \in ProcSet |-> CASE self = Loop -> "Bootstrap"
                                        [] self \in Workers -> "Work"]

Bootstrap == /\ pc[Loop] = "Bootstrap"
             /\ IF bootstrap_cycles < bootstrap_max_cycles
                   THEN /\ bootstrap_cycles' = bootstrap_cycles + 1
                        /\ IF Cardinality(RunnableWorkers(({})))  > 0
                              THEN /\ LET worker == CHOOSE x \in RunnableWorkers(({})): TRUE IN
                                        /\ started' = (started \cup {worker})
                                        /\ signal' = [signal EXCEPT ![worker] = "RUN"]
                              ELSE /\ TRUE
                                   /\ UNCHANGED << signal, started >>
                        /\ pc' = [pc EXCEPT ![Loop] = "Bootstrap"]
                   ELSE /\ pc' = [pc EXCEPT ![Loop] = "Cycle"]
                        /\ UNCHANGED << signal, bootstrap_cycles, started >>
             /\ UNCHANGED << workers_count, emitted_output, exit_status, 
                             worker_resources, bootstrap_max_cycles, exited, 
                             run_times, collected_output >>

Cycle == /\ pc[Loop] = "Cycle"
         /\ IF Cardinality(exited) < workers_count
               THEN /\ \E w \in RunningWorkers:
                         LET status == exit_status[w] IN
                           LET over_time == run_times[w] > 2 IN
                             LET fail == FastFailure /\ (over_time \/ status = 1) IN
                               /\ collected_output' = emitted_output
                               /\ emitted_output' = 0
                               /\ run_times' = [run_times EXCEPT ![w] = run_times[w] + 1]
                               /\ IF fail
                                     THEN /\ exit_status' =            [w |-> status]
                                                            @@ [x \in RunningWorkers |-> 9]
                                                            @@ exit_status
                                          /\ exited' = (exited \cup RunningWorkers)
                                          /\ pc' = [pc EXCEPT ![Loop] = "Complete"]
                                          /\ UNCHANGED << signal, started >>
                                     ELSE /\ IF (status \in {0, 1})
                                                THEN /\ exited' = (exited \cup {w})
                                                     /\ IF Cardinality(RunnableWorkers(({w})))  > 0
                                                           THEN /\ LET worker == CHOOSE x \in RunnableWorkers(({w})): TRUE IN
                                                                     /\ started' = (started \cup {worker})
                                                                     /\ signal' = [signal EXCEPT ![worker] = "RUN"]
                                                           ELSE /\ TRUE
                                                                /\ UNCHANGED << signal, 
                                                                                started >>
                                                     /\ UNCHANGED exit_status
                                                ELSE /\ IF over_time
                                                           THEN /\ exit_status' = [exit_status EXCEPT ![w] = 9]
                                                                /\ exited' = (exited \cup {w})
                                                                /\ IF Cardinality(RunnableWorkers(({w})))  > 0
                                                                      THEN /\ LET worker == CHOOSE x \in RunnableWorkers(({w})): TRUE IN
                                                                                /\ started' = (started \cup {worker})
                                                                                /\ signal' = [signal EXCEPT ![worker] = "RUN"]
                                                                      ELSE /\ TRUE
                                                                           /\ UNCHANGED << signal, 
                                                                                           started >>
                                                           ELSE /\ TRUE
                                                                /\ UNCHANGED << exit_status, 
                                                                                signal, 
                                                                                started, 
                                                                                exited >>
                                          /\ pc' = [pc EXCEPT ![Loop] = "Cycle"]
               ELSE /\ pc' = [pc EXCEPT ![Loop] = "Complete"]
                    /\ UNCHANGED << emitted_output, exit_status, signal, 
                                    started, exited, run_times, 
                                    collected_output >>
         /\ UNCHANGED << workers_count, worker_resources, bootstrap_cycles, 
                         bootstrap_max_cycles >>

Complete == /\ pc[Loop] = "Complete"
            /\ Assert((Cardinality(started) >= 1), 
                      "Failure of assertion at line 88, column 9.")
            /\ Assert((Cardinality(started) = Cardinality(exited)), 
                      "Failure of assertion at line 89, column 9.")
            /\ Assert((Cardinality(RunningWorkers) = 0), 
                      "Failure of assertion at line 90, column 9.")
            /\ Assert((emitted_output = 0), 
                      "Failure of assertion at line 91, column 9.")
            /\ IF FastFailure = FALSE
                  THEN /\ Assert((Cardinality(started) = workers_count), 
                                 "Failure of assertion at line 93, column 13.")
                  ELSE /\ TRUE
            /\ pc' = [pc EXCEPT ![Loop] = "Done"]
            /\ UNCHANGED << workers_count, emitted_output, exit_status, signal, 
                            worker_resources, bootstrap_cycles, 
                            bootstrap_max_cycles, started, exited, run_times, 
                            collected_output >>

loop == Bootstrap \/ Cycle \/ Complete

Work(self) == /\ pc[self] = "Work"
              /\ signal[self] = "RUN"
              /\ IF exit_status[self] /= NULL
                    THEN /\ pc' = [pc EXCEPT ![self] = "Done"]
                         /\ UNCHANGED emitted_output
                    ELSE /\ emitted_output' = emitted_output + 1
                         /\ pc' = [pc EXCEPT ![self] = "Exit"]
              /\ UNCHANGED << workers_count, exit_status, signal, 
                              worker_resources, bootstrap_cycles, 
                              bootstrap_max_cycles, started, exited, run_times, 
                              collected_output >>

Exit(self) == /\ pc[self] = "Exit"
              /\ IF exit_status[self] /= NULL
                    THEN /\ pc' = [pc EXCEPT ![self] = "Done"]
                         /\ UNCHANGED << emitted_output, exit_status >>
                    ELSE /\ emitted_output' = emitted_output + 1
                         /\ \/ /\ exit_status' = [exit_status EXCEPT ![self] = 0]
                            \/ /\ exit_status' = [exit_status EXCEPT ![self] = 1]
                         /\ pc' = [pc EXCEPT ![self] = "Done"]
              /\ UNCHANGED << workers_count, signal, worker_resources, 
                              bootstrap_cycles, bootstrap_max_cycles, started, 
                              exited, run_times, collected_output >>

worker(self) == Work(self) \/ Exit(self)

(* Allow infinite stuttering to prevent deadlock on termination. *)
Terminating == /\ \A self \in ProcSet: pc[self] = "Done"
               /\ UNCHANGED vars

Next == loop
           \/ (\E self \in Workers: worker(self))
           \/ Terminating

Spec == /\ Init /\ [][Next]_vars
        /\ WF_vars(loop)

Termination == <>(\A self \in ProcSet: pc[self] = "Done")

\* END TRANSLATION 

ParallelismRespected == Cardinality(RunningWorkers) <= Parallelism
LoopTerminates == <>[](pc[Loop] = "Done")
=============================================================================
\* Modification History
\* Last modified Mon May 30 18:00:29 CEST 2022 by lucatume
\* Created Sat May 14 09:54:59 CEST 2022 by lucatume
