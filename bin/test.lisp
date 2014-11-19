(car '(0 0)) ;should be 0

exit

(cdr '(0 0)) ;should be (0)

(cdr (cdr '(1 2 3 4))) ;should be (3 4)

(car (cdr (cdr '(1 2 3)))) ;should be 3

(car nil) ;should be nil

(cdr nil) ;should be nil

(car (cdr (car nil))) ;should be nil

10000000 ;should be 10000000

(+ 3 4) ;should be 7

(- 20 5) ;should be 15

(* 100 5 (/ 3 2)) ;should be 750

(+ 3 4 (+ 3 4 (+ (+ 2 3))) 1) ;should be 20

(* (+ 3 4) (+ 3 4)) ;should be 49

(/ (+ 3 4) (+ 3 4)) ;should be 1

(defun test (x y z) 
  (setf test (+ x y))
  (- z test)
  )

(test 1 5 21) ;should be 15

(test 3 0 2) ;should be -1


((lambda (x) (+ x 2)) 9) ; should be 11

(debug t)


(setf test (+ 1 2)) ;should be 3

test ;should be 3

(cond (nil 1) ((not nil) 2) (3)) ;should be 2

(cond (nil)) ; should be nil

(cons 1 t)

(setf file (open "input.txt" "r"))
(read-line file)
(close file)

(defun fib (n)
  (cond ((eql n 0) 0)
	((eql n 1) 1)
	(t (+ (fib (- n 1)) (fib (- n 2))))
	))

(fib 6)
